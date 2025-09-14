type propsType = {
  message: string;
};

const ExampleComponent = (props: propsType) => {
  return (
    <>
      <p>{props.message}</p>
    </>
  );
};

export default ExampleComponent;
