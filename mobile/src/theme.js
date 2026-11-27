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
  ink: '#151A2E',
  camera: '#0B1020',
};

export const LIGHT_COLORS = {
  ...shared,
  background: '#F7F8FA',
  surface: '#FFFFFF',
  surfaceAlt: '#EEF0F4',
  text: '#151A2E',
  textMuted: '#566078',
  border: '#DEE2EA',
  successSurface: '#E7F4F1',
  successText: '#12504D',
  dangerSurface: '#FDECF0',
  dangerText: '#811E2D',
  warningSurface: '#FFF6DE',
  warningText: '#6F4200',
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
  successSurface: 'rgba(31, 122, 116, 0.20)',
  successText: '#8FD8CD',
  dangerSurface: 'rgba(196, 55, 75, 0.20)',
  dangerText: '#FF9CAB',
  warningSurface: 'rgba(254, 192, 9, 0.18)',
  warningText: '#FFD66A',
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
