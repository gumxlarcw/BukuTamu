module.exports = {
  apps: [
    {
      name: 'bukutamu-frontend',
      script: 'npx',
      args: 'serve dist -p 3060 --no-clipboard -c ../public/serve.json',
      cwd: '/var/www/html/bukutamu/frontend',
      interpreter: 'none',
    },
  ],
}
