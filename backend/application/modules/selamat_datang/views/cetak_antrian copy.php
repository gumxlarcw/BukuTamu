<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak Antrian</title>

    <script>
        // Masukkan Sha256 secara manual
        var Sha256 = function(m) {
            function f(n, r) { return (n >>> r) | (n << (32 - r)); }
            function h(n, r) { return (n >>> r); }
            function c(n, r, e) { return (n & r) ^ (~n & e); }
            function u(n, r, e) { return (n & r) ^ (n & e) ^ (r & e); }
            function d(n) { return h(n, 2) ^ h(n, 13) ^ h(n, 22); }
            function p(n) { return h(n, 6) ^ h(n, 11) ^ h(n, 25); }
            function l(n) { return h(n, 7) ^ h(n, 18) ^ (n >>> 3); }
            function v(n) { return h(n, 17) ^ h(n, 19) ^ (n >>> 10); }
            function g(n) {
                var r = [], e, o, t, i, a, h, f = [], g = [];
                for (n += String.fromCharCode(128), o = n.length / 4 + 2, t = Math.ceil(o / 16), e = new Array(t), i = 0; i < t; i++) {
                    e[i] = new Array(16);
                    for (a = 0; a < 16; a++) {
                        e[i][a] = n.charCodeAt(64 * i + 4 * a) << 24 |
                            n.charCodeAt(64 * i + 4 * a + 1) << 16 |
                            n.charCodeAt(64 * i + 4 * a + 2) << 8 |
                            n.charCodeAt(64 * i + 4 * a + 3) << 0;
                    }
                }
                e[t - 1][14] = 8 * (n.length - 1);
                e[t - 1][15] = (8 * (n.length - 1)) >>> 0;
                var s = [1779033703, 3144134277, 1013904242, 2773480762,
                        1359893119, 2600822924, 528734635, 1541459225],
                    w = [1116352408, 1899447441, 3049323471, 3921009573,
                        961987163, 1508970993, 2453635748, 2870763221,
                        3624381080, 310598401, 607225278, 1426881987,
                        1925078388, 2162078206, 2614888103, 3248222580,
                        3835390401, 4022224774, 264347078, 604807628,
                        770255983, 1249150122, 1555081692, 1996064986,
                        2554220882, 2821834349, 2952996808, 3210313671,
                        3336571891, 3584528711, 113926993, 338241895,
                        666307205, 773529912, 1294757372, 1396182291,
                        1695183700, 1986661051, 2177026350, 2456956037,
                        2730485921, 2820302411, 3259730800, 3345764771,
                        3516065817, 3600352804, 4094571909, 275423344,
                        430227734, 506948616, 659060556, 883997877,
                        958139571, 1322822218, 1537002063, 1747873779,
                        1955562222, 2024104815, 2227730452, 2361852424,
                        2428436474, 2756734187, 3204031479, 3329325298];
                for (i = 0; i < t; i++) {
                    for (a = 0; a < 16; a++) f[a] = e[i][a];
                    for (a = 16; a < 64; a++) f[a] = v(f[a - 2]) + f[a - 7] + l(f[a - 15]) + f[a - 16];
                    var m = s.slice();
                    for (a = 0; a < 64; a++) {
                        var y = m[7] + p(m[4]) + c(m[4], m[5], m[6]) + w[a] + f[a];
                        var z = d(m[0]) + u(m[0], m[1], m[2]);
                        m = [y + z].concat(m.slice(0, 7));
                        m[4] += y;
                    }
                    for (a = 0; a < 8; a++) s[a] += m[a];
                }
                for (var b = "", a = 0; a < 8; a++) {
                    for (i = 3; i + 1; i--) {
                        var x = (s[a] >> 8 * i) & 255;
                        b += ((x < 16) ? "0" : "") + x.toString(16);
                    }
                }
                return b;
            }
            return { hash: g };
        }();
    </script>

    <!-- Wajib: SHA256 + RSVP + QZ Tray -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rsvp/4.8.5/rsvp.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <script>
        qz.security.setCertificatePromise(() => {
            return fetch("https://bukutamu.bpsmalut.com/assets/qz/qz-certificate.pem").then(res => res.text());
        });

        qz.security.setSignaturePromise(function(toSign) {
            return function(resolve, reject) {
                fetch("https://bukutamu.bpsmalut.com/assets/qz/sign.php?request=" + encodeURIComponent(toSign))
                    .then(res => res.text())
                    .then(sig => {
                        console.log("🔏 Signature berhasil:", sig.substring(0, 20) + "...");
                        resolve(sig);
                    })
                    .catch(reject);
            };
        });
    </script>

    <style>
        body {
            font-family: monospace;
            text-align: center;
            padding: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h2>BPS Provinsi Maluku Utara</h2>
    <h3>No. Antrian</h3>
    <h1><?= $no_antrian ?></h1>
    <p><?= date('d/m/Y H:i') ?></p>
    <p>Terima kasih</p>
    <script>
        const noAntrian = "<?= $no_antrian ?>";
        const waktu = "<?= date('d/m/Y H:i') ?>";

        window.onload = function () {
            if (typeof qz === 'undefined') {
                alert("❌ QZ Tray belum siap (qz is undefined).");
                return;
            }

            const noAntrian = "<?= $no_antrian ?>";
            const waktu = "<?= date('d/m/Y H:i') ?>";

            function mulaiCetak() {
                console.log("📦 Memulai proses cetak...");
                qz.printers.find("POS-58").then((printer) => {
                    console.log("🖨️ Printer ditemukan:", printer);
                    const cfg = qz.configs.create(printer);
                    const data = [
                        '\x1B\x40',
                        '\x1B\x61\x01',
                        'BPS Provinsi Maluku Utara\n',
                        '------------------------\n',
                        'Nomor Antrian\n',
                        '\x1B\x21\x30',
                        noAntrian + '\n',
                        '\x1B\x21\x00',
                        waktu + '\n',
                        '------------------------\n',
                        'Terima kasih Telah Berkunjung\n\n\n',
                        '\x1D\x56\x00'
                    ];
                    console.log("🖨️ Menyiapkan cetak...");
                    return qz.print(cfg, data);
                }).then(() => {
                    console.log("✅ Sukses mencetak");
                    window.close();
                }).catch((err) => {
                    alert("❌ Gagal mencetak: " + err);
                    console.error(err);
                });
            }

            if (!qz.websocket.isActive()) {
                console.log("🔌 QZ WebSocket belum terhubung. Menghubungkan...");
                qz.websocket.connect().then(() => {
                    console.log("✅ QZ WebSocket berhasil terhubung.");
                    mulaiCetak();
                }).catch(err => {
                    alert("❌ Tidak bisa konek QZ Tray: " + err);
                    console.error(err);
                });
            } else {
                console.log("✅ QZ WebSocket sudah aktif.");
                mulaiCetak();
            }
        };
    </script>
</body>
</html>
