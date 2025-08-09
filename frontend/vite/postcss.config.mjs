import autoprefixer from 'autoprefixer';
import cssDeclarationSorter from 'css-declaration-sorter';
import tailwindcss from 'tailwindcss';

export default {
  plugins: {
    autoprefixer: autoprefixer(),
    'css-declaration-sorter': cssDeclarationSorter({ order: 'smacss' }),
    tailwindcss: tailwindcss(),
  },
};


