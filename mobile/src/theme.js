import { useColorScheme } from 'react-native';

const shared = {
  primary: '#FEC009',
  primaryDark: '#D9A200',
  primaryText: '#151A2E',
  accent: '#1F7A74',
  accentDark: '#17625E',
  onAccent: '#FFFFFF',
  success: '#1F7A74',
  danger: '#C4374B',
  warning: '#B26A00',
  white: '#FFFFFF',
};

export const LIGHT_COLORS = {
  ...shared,
  background: '#F7F8FA',
  surface: '#FFFFFF',
  surfaceAlt: '#EEF0F4',
  text: '#151A2E',
  textMuted: '#566078',
  border: '#DEE2EA',
  overlay: 'rgba(13, 17, 32, 0.55)',
  statusBar: 'dark',
};

export const DARK_COLORS = {
  ...shared,
  background: '#0D1120',
  surface: '#151A2E',
  surfaceAlt: '#1C2238',
  text: '#F7F8FA',
  textMuted: '#9BA4BF',
  border: '#333D5B',
  overlay: 'rgba(0, 0, 0, 0.65)',
  statusBar: 'light',
};

export function useTheme() {
  const scheme = useColorScheme();
  const dark = scheme === 'dark';

  return {
    dark,
    colors: dark ? DARK_COLORS : LIGHT_COLORS,
  };
}
