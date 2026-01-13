import type { Meta, StoryObj } from '@storybook/react';
import { Button } from '../components/tsx/Button';

const meta: Meta<typeof Button> = {
  title: 'Components/Button',
  component: Button,
  tags: ['autodocs'],
  args: {
    htmlElement: 'button',
    children: 'Button',
    variant: 'primary',
    size: 'default',
    display: 'inline',
    shape: 'rounded',
    state: 'normal',
    color: 'default',
  },
  argTypes: {
    color: {
      control: 'select',
      options: ['default', 'red', 'orange', 'orange-yellow', 'yellow', 'yellow-green', 'green', 'teal', 'cyan', 'light-blue', 'blue', 'blue-violet', 'violet', 'purple', 'magenta', 'pink', 'pink-red'],
    },
  },
  parameters: {
    layout: 'padded',
  },
  
};

export default meta;

type Story = StoryObj<typeof Button>;

export const Primary: Story = {
  args: {
    children: 'Button',
    variant: 'primary',
  },
};

export const Secondary: Story = {
  args: {
    children: 'Button',
    variant: 'secondary',
  },
};
