import { Noto_Sans_JP } from 'next/font/google';
// import localFont from 'next/font/local';

export const notoSansJP = Noto_Sans_JP({
  subsets: ['latin'],
  variable: '--font-noto-sans-jp',
  weight: 'variable',
  display: 'swap',
  preload: true,
});

// export const customFont = localFont({
//   variable: '--font-custom',
//   src: [
//     {
//       path: '/fonts/custom-font-100.woff2',
//       weight: '100',
//       style: 'normal'
//     },
//     {
//       path: '/fonts/custom-font-200.woff2',
//       weight: '200',
//       style: 'normal'
//     },
//     {
//       path: '/fonts/custom-font-300.woff2',
//       weight: '300',
//       style: 'normal'
//     },
//     {
//       path: '/fonts/custom-font-400.woff2',
//       weight: '400',
//       style: 'normal'
//     },
//     {
//       path: '/fonts/custom-font-500.woff2',
//       weight: '500',
//       style: 'normal'
//     },
//     {
//       path: '/fonts/custom-font-700.woff2',
//       weight: '700',
//       style: 'normal'
//     },
//     {
//       path: '/fonts/custom-font-800.woff2',
//       weight: '800',
//       style: 'normal'
//     },
//     {
//       path: '/fonts/custom-font-900.woff2',
//       weight: '900',
//       style: 'normal'
//     }
//   ]
// });
