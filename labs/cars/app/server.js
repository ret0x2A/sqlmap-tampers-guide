const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const path = require('path');

const authRoutes = require('./auth');
const carRoutes = require('./cars');

const app = express();
app.use(bodyParser.json());
app.use(cors());

app.use('/oauth', authRoutes);
app.use('/cars', carRoutes);

app.use(express.static(path.join(__dirname, 'static')));

app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ error: 'Internal server error' });
});

const port = 3000;
app.listen(port, () => console.log(`Server running on port ${port}`));