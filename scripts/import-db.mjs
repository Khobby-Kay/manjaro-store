import mysql from 'mysql2/promise';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const config = {
  host: process.env.DB_HOST,
  port: Number(process.env.DB_PORT || 3306),
  user: process.env.DB_USERNAME,
  password: process.env.DB_PASSWORD,
  multipleStatements: true,
};

const dbName = process.env.DB_DATABASE || 'manjaro_store';
const sqlPath = path.join(__dirname, '..', 'public', 'demo.sql');

async function main() {
  const conn = await mysql.createConnection(config);
  await conn.query(`CREATE DATABASE IF NOT EXISTS \`${dbName}\``);
  await conn.query(`USE \`${dbName}\``);
  const sql = fs.readFileSync(sqlPath, 'utf8');
  await conn.query(sql);
  await conn.end();
  console.log(`Imported demo.sql into ${dbName}`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
