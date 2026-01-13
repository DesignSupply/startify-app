import React from 'react';

type HtmlElement = 'button' | 'a';
type Variant = 'primary' | 'secondary';
type Size = 'small' | 'default' | 'large';
type Display = 'block' | 'inline';
type Shape = 'square' | 'rounded' | 'pill';
type State = 'normal' | 'hover' | 'active' | 'focus' | 'disabled';

type BaseProps = {
  htmlElement?: HtmlElement;
  variant?: Variant;
  size?: Size;
  display?: Display;
  shape?: Shape;
  state?: State;
  className?: string;
  children?: React.ReactNode;
};

type ButtonElementProps = BaseProps &
  React.ButtonHTMLAttributes<HTMLButtonElement> & { htmlElement?: 'button' };

type AnchorElementProps = BaseProps &
  React.AnchorHTMLAttributes<HTMLAnchorElement> & { htmlElement: 'a' };

export type ButtonProps = ButtonElementProps | AnchorElementProps;

export const Button = React.forwardRef<HTMLButtonElement | HTMLAnchorElement, ButtonProps>(
  function Button(
    {
      htmlElement = 'button',
      variant = 'primary',
      size = 'default',
      display = 'inline',
      shape = 'rounded',
      state = 'normal',
      className = '',
      children,
      ...rest
    },
    ref,
  ) {
    const isAnchor = htmlElement === 'a';
    const isDisabled = state === 'disabled';
    const classes = [
      `su-button-${variant}`,
      `su-button-size-${size}`,
      display === 'inline' ? '' : 'su-button-display-block',
      `su-button-shape-${shape}`,
      `su-button-state-${state}`,
      className,
    ]
      .filter(Boolean)
      .join(' ');

    if (isAnchor) {
      const anchorRest = rest as React.AnchorHTMLAttributes<HTMLAnchorElement>;
      const handleClick: React.MouseEventHandler<HTMLAnchorElement> = (event) => {
        if (isDisabled) {
          event.preventDefault();
          event.stopPropagation();
          return;
        }
        anchorRest.onClick?.(event);
      };

      return (
        <a
          ref={ref as React.Ref<HTMLAnchorElement>}
          className={classes}
          aria-disabled={isDisabled}
          tabIndex={isDisabled ? -1 : anchorRest.tabIndex}
          onClick={handleClick}
          href={anchorRest.href ?? '#'}
          {...anchorRest}
        >
          {children}
        </a>
      );
    }

    const buttonRest = rest as React.ButtonHTMLAttributes<HTMLButtonElement>;

    return (
      <button
        ref={ref as React.Ref<HTMLButtonElement>}
        className={classes}
        type={buttonRest.type ?? 'button'}
        disabled={isDisabled}
        {...buttonRest}
      >
        {children}
      </button>
    );
  },
);

export default Button;
