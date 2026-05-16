module.exports = {
  apps: [
    {
      name: 'tamdes-print',
      script: 'server.js',
      cwd: '/var/www/html/tamdes-print',
      env: {
        PRINT_PORT: '5300',
        PRINT_DEVICE: '/dev/usb/lp0',
        PRINTER_NAME: 'POS-58',
        PRINT_LOG: '/var/log/tamdes-print.log',
      },
      max_memory_restart: '128M',
      autorestart: true,
    },
  ],
}
