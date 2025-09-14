declare module '/@react-refresh' {
  const RefreshRuntime: {
    injectIntoGlobalHook: (win: any) => void;
  };
  export default RefreshRuntime;
}
