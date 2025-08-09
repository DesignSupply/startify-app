interface propsInterface {
  message: string;
}

const ExampleComponent = (props: propsInterface): React.JSX.Element => {
  return (
    <>
      <p>{props.message}</p>
    </>
  );
};

export default ExampleComponent;
