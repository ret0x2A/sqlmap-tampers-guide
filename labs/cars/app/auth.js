const express = require('express');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const { v4: uuidv4 } = require('uuid');
const db = require('./db');

const expiriesToken = '15s'
const expiriesRefresh = 300000

const router = express.Router();

async function deleteTokenFromDB(token) {
  await db.query('DELETE FROM refresh_tokens WHERE token=?', [token]);
}

// Register
router.post('/register', async (req, res) => {
  const { username, password } = req.body;
  const hash = await bcrypt.hash(password, 10);
  try {
    await db.query('INSERT INTO users (username, password_hash) VALUES (?,?)', [username, hash]);
    res.json({ message: 'Registered' });
  } catch (err) {
    res.status(400).json({ error: 'User exists or DB error' });
  }
});

// Token
router.post('/token', async (req, res) => {
  console.log('JWT_SECRET:', process.env.JWT_SECRET);
  const { username, password } = req.body;
  const [rows] = await db.query('SELECT * FROM users WHERE username=?', [username]);
  if (!rows.length) return res.status(401).json({ error: 'Invalid creds' });
  const user = rows[0];
  const ok = await bcrypt.compare(password, user.password_hash);
  if (!ok) return res.status(401).json({ error: 'Invalid creds' });
  
  const accessToken = jwt.sign({ sub: user.id }, process.env.JWT_SECRET, { expiresIn: expiriesToken });
  const refreshToken = uuidv4();
  const expiresAt = new Date(Date.now() + expiriesRefresh); 

  await db.query('INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?,?,?)',
    [user.id, refreshToken, expiresAt]);

  res.json({ access_token: accessToken, refresh_token: refreshToken });
});

// Refresh
router.post('/refresh', async (req, res) => {
  console.log('JWT_SECRET:', process.env.JWT_SECRET);
  const { refresh_token } = req.body;
  const [rows] = await db.query('SELECT * FROM refresh_tokens WHERE token=? AND revoked=0', [refresh_token]);
  if (!rows.length) return res.status(401).json({ error: 'Invalid refresh' });
  const tokenRow = rows[0];
  if (new Date(tokenRow.expires_at) < new Date()) return res.status(401).json({ error: 'Expired refresh' });

  // revoke old
  await db.query('UPDATE refresh_tokens SET revoked=1 WHERE id=?', [tokenRow.id]);

  const accessToken = jwt.sign({ sub: tokenRow.user_id }, process.env.JWT_SECRET, { expiresIn: expiriesToken });
  const newRefresh = uuidv4();
  const expiresAt = new Date(Date.now() + expiriesRefresh);
  await db.query('INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?,?,?)',
    [tokenRow.user_id, newRefresh, expiresAt]);

  res.json({ access_token: accessToken, refresh_token: newRefresh });
});

router.get('/check', async (req, res) => {
  console.log('JWT_SECRET:', process.env.JWT_SECRET);
  const auth = req.headers['authorization'];
  console.log('First check good')
  if (!auth) return res.status(401).json({ error: 'No token' });
    const token = auth.split(' ')[1];
    try {
      const decoded = jwt.verify(token, process.env.JWT_SECRET);
      console.log('Seconf check', decoded)
      return res.status(200).json({ok: 'ok'})
    } catch (e) {
      console.log(e)
      return res.status(401).json({ error: 'Invalid token' });
    }
})

router.get('/logout', async (req, res) => {
  const auth = req.headers['authorization'];

  if (!auth) return res.status(401).json({ error: 'No token' });
  
  const token = auth.split(' ')[1];
  deleteTokenFromDB(token);

})

module.exports = router;
