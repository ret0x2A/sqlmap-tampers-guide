const express = require('express');
const jwt = require('jsonwebtoken');
const db = require('./db');

const router = express.Router();

function authMiddleware(req, res, next) {
  const auth = req.headers['authorization'];
  console.log('First check good')
  console.log('JWT_SECRET:', process.env.JWT_SECRET);
  if (!auth) return res.status(401).json({ error: 'No token' });
  const token = auth.split(' ')[1];
  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    console.log('Seconf check', decoded)
    req.user = decoded.sub;
    next();
  } catch (e) {
    console.log(e)
    return res.status(401).json({ error: 'Invalid token' });
  }
}

// CRUD protected
router.use(authMiddleware);

router.get('/', async (req, res) => {
  const [rows] = await db.query('SELECT * FROM cars');
  res.json(rows);
});

router.post('/', async (req, res) => {
  const { name, make, model, year, description } = req.body;
  await db.query('INSERT INTO cars (name, make, model, year, description) VALUES (?,?,?,?,?)',
    [name, make, model, year, description]);
  res.json({ message: 'Car added' });
});

router.put('/:id', async (req, res) => {
  const { name, make, model, year, description } = req.body;
  await db.query('UPDATE cars SET name=?, make=?, model=?, year=?, description=? WHERE id=?',
    [name, make, model, year, description, req.params.id]);
  res.json({ message: 'Car updated' });
});

router.delete('/:id', async (req, res) => {
  await db.query('DELETE FROM cars WHERE id=?', [req.params.id]);
  res.json({ message: 'Car deleted' });
});

// Vulnerable search
router.get('/search', async (req, res) => {
  console.log('Search cars')
  console.log('Raw query param:', req.query.q);
  const q = req.query.q || '';
  const unsafeQuery = `SELECT * FROM cars WHERE name LIKE '%${q}%'`; // vulnerable
  console.log(`Unsafe Query; ${unsafeQuery}`);
  try {
    const [rows] = await db.query(unsafeQuery);
    res.json(rows);
  } catch(e) {
    return res.status(400).json({error: 'Bad Request'});
  }
});

router.get('/:id', async (req, res) => {
  console.log('Get car by Id')
  const [rows] = await db.query('SELECT * FROM cars WHERE id=?', [req.params.id]);
  res.json(rows[0] || {});
});

module.exports = router;
