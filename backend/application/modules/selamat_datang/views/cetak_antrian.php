<?php
$no = isset($no_antrian) ? (string) $no_antrian : '';
?>

<div style="font-family: Arial, sans-serif; padding: 16px;">
  <h3>Nomor Antrian</h3>
  <p style="font-size: 20px;"><b><?= htmlspecialchars($no, ENT_QUOTES, 'UTF-8') ?></b></p>

  <button onclick="kirimCetak()" style="padding:10px 14px;">🖨️ Cetak Antrian</button>
</div>

<script>
const NO_ANTRIAN = "<?= htmlspecialchars($no, ENT_QUOTES, 'UTF-8') ?>";

function kirimCetak() {
  if (!NO_ANTRIAN) {
    alert('❌ Nomor antrian tidak tersedia.');
    return;
  }

  fetch('http://localhost:5000/print', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ no: NO_ANTRIAN })
  })
  .then(res => res.text())
  .then(msg => alert("✅ " + msg))
  .catch(err => alert("❌ Gagal: " + err));
}
</script>
