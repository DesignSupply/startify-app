import { useSomeContext } from '@/react/App';

type propsType = {
  message: string;
};

const ExampleComponent = (props: propsType) => {
  const context = useSomeContext();
  return (
    <>
      <p>{props.message}</p>
    </>
  );
};

export default ExampleComponent;
