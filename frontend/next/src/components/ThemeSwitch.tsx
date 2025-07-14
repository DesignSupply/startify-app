'use client';

import { useSiteThemeContext, stateType } from '@/contexts/siteThemeContext';

export default function ThemeSwitch() {
  const { state, setState } = useSiteThemeContext();

  const changeHandler = (event: React.ChangeEvent<HTMLInputElement>) => {
    const newTheme = event.target.value as stateType['currentTheme'];
    setState((prev) => ({
      ...prev,
      currentTheme: newTheme,
    }));
  };

  return (
    <>
      <label>
        <input
          type="radio"
          name="theme"
          value="light"
          aria-label="Light Mode"
          onChange={changeHandler}
          checked={state.currentTheme === 'light'}
        />
        ライト
      </label>
      <label>
        <input
          type="radio"
          name="theme"
          value="dark"
          aria-label="Dark Mode"
          onChange={changeHandler}
          checked={state.currentTheme === 'dark'}
        />
        ダーク
      </label>
    </>
  );
}
