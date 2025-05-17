
const express = require('express');
const fetch = require('node-fetch');
const rateLimit = require('express-rate-limit');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

const limiter = rateLimit({
  windowMs: 60 * 1000,
  max: 5,
  message: { status: 429, message: 'Terlalu banyak permintaan. Coba lagi nanti.' }
});

app.use(limiter);
app.use(express.static('.'));

app.get('/api/track', async (req, res) => {
  const { resi, courier } = req.query;
  if (!resi || !courier) return res.json({ status: 400, message: 'Parameter tidak lengkap' });

  const url = `https://api.binderbyte.com/v1/track?api_key=${process.env.BINDERBYTE_KEY}&courier=${courier}&awb=${resi}`;
  try {
    const response = await fetch(url);
    const data = await response.json();

    console.log(`[LOG] ${new Date().toISOString()} - ${courier} - ${resi} - ${data.status}`);

    res.json(data);
  } catch (err) {
    res.json({ status: 500, message: 'Kesalahan server', error: err.message });
  }
});

app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
});
