import { useMatches } from 'react-router-dom';

const Home = () => {
  const name = (useMatches().at(-1)?.handle as { name: string }).name;
  return (
    <>
      <h1>Current page is {name} (React Router)</h1>
    </>
  );
};

export default Home;
