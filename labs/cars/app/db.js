const mysql = require('mysql2/promise');

const pool = mysql.createPool({
  host: process.env.DB_HOST || 'db',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASS || 'rootpass',
  database: process.env.DB_NAME || 'car_catalog'
});

module.exports = pool;
