import autoprefixer from 'autoprefixer';
import cssDeclarationSorter from 'css-declaration-sorter';
import tailwindcss from '@tailwindcss/postcss';

export default {
  plugins: [
    tailwindcss(),
    autoprefixer(),
    cssDeclarationSorter({ order: 'smacss' }),
  ],
};