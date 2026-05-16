module.exports = {
  apps: [
    {
      name: 'tamdes-frontend',
      script: 'npx',
      args: 'serve dist -p 3060 --no-clipboard -c ../public/serve.json',
      cwd: '/var/www/html/tamdes-frontend',
      interpreter: 'none',
    },
  ],
}
