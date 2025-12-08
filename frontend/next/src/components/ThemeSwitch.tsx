'use client';

import { useSiteThemeStore, type ThemeMode } from '@/stores/siteThemeStore';
// import { useSiteThemeContext, stateType } from '@/contexts/siteThemeContext'; // context

export default function ThemeSwitch() {
  const theme = useSiteThemeStore((state) => state.currentTheme);
  const setTheme = useSiteThemeStore((state) => state.setTheme);
  // const { state, setState } = useSiteThemeContext(); // context

  const changeHandler = (event: React.ChangeEvent<HTMLInputElement>) => {
    const newTheme = event.target.value as ThemeMode;
    setTheme(newTheme);
    // const legacyTheme = event.target.value as stateType['currentTheme']; // context
    // setState((prev) => ({ ...prev, currentTheme: legacyTheme })); // context
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
          checked={theme === 'light'}
        />
        ライト
        {/* context: checked={state.currentTheme === 'light'} */}
      </label>
      <label>
        <input
          type="radio"
          name="theme"
          value="dark"
          aria-label="Dark Mode"
          onChange={changeHandler}
          checked={theme === 'dark'}
        />
        ダーク
        {/* context: checked={state.currentTheme === 'dark'} */}
      </label>
    </>
  );
}
