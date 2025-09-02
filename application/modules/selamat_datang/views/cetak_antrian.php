<script>
function kirimCetak() {
  fetch('http://localhost:5000/print', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ text: "Nomor Antrian A005" })
  })
  .then(res => res.text())
  .then(msg => alert("✅ " + msg))
  .catch(err => alert("❌ Gagal: " + err));
}
</script>

<button onclick="kirimCetak()">🖨️ Cetak Antrian</button>
