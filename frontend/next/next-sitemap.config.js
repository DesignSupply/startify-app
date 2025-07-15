/** @type {import('next-sitemap').IConfig} */
module.exports = {
  siteUrl: process.env.APPURL || 'https://example.com', // 本番環境の公開ドメイン
  generateRobotsTxt: false,
} 