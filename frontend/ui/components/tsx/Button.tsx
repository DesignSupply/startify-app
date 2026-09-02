import React from 'react';

type HtmlElement = 'button' | 'a';
type Variant = 'primary' | 'secondary';
type Size = 'small' | 'default' | 'large';
type Display = 'block' | 'inline';
type Shape = 'square' | 'rounded' | 'pill';
type State = 'normal' | 'hover' | 'active' | 'focus' | 'disabled';
type Color = 'default' | 'red' | 'orange' | 'orange-yellow' | 'yellow' | 'yellow-green' | 'green' | 'teal' | 'cyan' | 'light-blue' | 'blue' | 'blue-violet' | 'violet' | 'purple' | 'magenta' | 'pink' | 'pink-red';

type BaseProps = {
  htmlElement?: HtmlElement;
  variant?: Variant;
  size?: Size;
  display?: Display;
  shape?: Shape;
  state?: State;
  color?: Color;
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
      color = 'default',
      className = '',
      children,
      ...rest
    },
    ref,
  ) {
    const isAnchor = htmlElement === 'a';
    const buttonRest = rest as React.ButtonHTMLAttributes<HTMLButtonElement>;
    const isDisabled = state === 'disabled' || (!isAnchor && buttonRest.disabled === true);
    const classes = [
      color !== 'default' ? variant === 'primary' ? `su-button-fill-${color}` : `su-button-outline-${color}` : `su-button-${variant}`,
      size !== 'default' ? `su-button-size-${size}` : '',
      display === 'inline' ? '' : 'su-button-display-block',
      `su-button-shape-${shape}`,
      isDisabled ? 'su-button-state-disabled' : state !== 'normal' ? `su-button-state-${state}` : '',
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
          {...anchorRest}
          ref={ref as React.Ref<HTMLAnchorElement>}
          className={classes}
          aria-disabled={isDisabled}
          tabIndex={isDisabled ? -1 : anchorRest.tabIndex}
          onClick={handleClick}
          href={isDisabled ? undefined : anchorRest.href ?? '#'}
        >
          {children}
        </a>
      );
    }

    return (
      <button
        {...buttonRest}
        ref={ref as React.Ref<HTMLButtonElement>}
        className={classes}
        type={buttonRest.type ?? 'button'}
        disabled={isDisabled}
      >
        {children}
      </button>
    );
  },
);

export default Button;
