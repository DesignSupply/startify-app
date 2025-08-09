interface propsInterface {
  message: string;
}

const ExampleComponent = (props: propsInterface): JSX.Element => {
  return (
    <>
      <p>{props.message}</p>
    </>
  );
};

export default ExampleComponent;
