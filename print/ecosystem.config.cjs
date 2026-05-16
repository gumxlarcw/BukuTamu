module.exports = {
  apps: [
    {
      name: 'bukutamu-print',
      script: 'server.js',
      cwd: '/var/www/html/bukutamu/print',
      env: {
        PRINT_PORT: '5300',
        PRINT_DEVICE: '/dev/usb/lp0',
        PRINTER_NAME: 'POS-58',
        PRINT_LOG: '/var/log/bukutamu-print.log',
      },
      max_memory_restart: '128M',
      autorestart: true,
    },
  ],
}
