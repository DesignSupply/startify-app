import { useEffect, useState, useContext, useRef } from 'react';
import ExampleComponent from '@/react/components/ExampleComponent';
import { storeContext } from '@/react/storeContext';

const App = () => {
  const store = useContext(storeContext)!;
  const [text, setText] = useState('This is ExampleComponent (React)');
  const didEffect = useRef(false);

  useEffect(() => {
    if (import.meta.env.DEV && didEffect.current) return;
    didEffect.current = true;
    store?.updateMessage('state updated.');
    console.log('React is ready. state updated.');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <>
      <ExampleComponent message={text} />
    </>
  );
};

export default App;
