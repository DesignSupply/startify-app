import autoprefixer from 'autoprefixer';
import cssDeclarationSorter from 'css-declaration-sorter';
import tailwindcss from 'tailwindcss';

export default {
  plugins: [
    tailwindcss({ config: './tailwind.config.mjs' }),
    autoprefixer(),
    cssDeclarationSorter({ order: 'smacss' }),
  ],
};
