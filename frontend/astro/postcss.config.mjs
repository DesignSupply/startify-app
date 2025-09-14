import autoprefixer from 'autoprefixer';
import cssDeclarationSorter from 'css-declaration-sorter';

export default {
  plugins: [
    autoprefixer(),
    cssDeclarationSorter({ order: 'smacss' }),
  ],
};
