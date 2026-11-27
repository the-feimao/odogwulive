import React, { useMemo } from 'react';
import {
  ActivityIndicator,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';

import { useTheme } from '../theme';

function makeStyles(colors) {
  return StyleSheet.create({
    button: {
      minHeight: 52,
      borderRadius: 16,
      paddingHorizontal: 18,
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'center',
      gap: 9,
    },
    buttonPrimary: { backgroundColor: colors.primary },
    buttonSecondary: { backgroundColor: colors.accent },
    buttonDark: { backgroundColor: colors.ink },
    buttonDanger: { backgroundColor: colors.danger },
    buttonGhost: {
      backgroundColor: colors.surface,
      borderWidth: 1,
      borderColor: colors.border,
    },
    buttonPressed: { opacity: 0.78 },
    buttonDisabled: { opacity: 0.5 },
    buttonText: { fontSize: 15, fontWeight: '800', letterSpacing: 0.1 },
    buttonTextDark: { color: colors.white },
    buttonTextPrimary: { color: colors.primaryText },
    buttonTextSecondary: { color: colors.onAccent },
    buttonTextGhost: { color: colors.text },
    card: {
      backgroundColor: colors.surface,
      borderRadius: 20,
      borderWidth: 1,
      borderColor: colors.border,
      padding: 18,
      shadowColor: colors.ink,
      shadowOpacity: 0.05,
      shadowRadius: 12,
      shadowOffset: { width: 0, height: 4 },
      elevation: 2,
    },
    cardDark: {
      backgroundColor: colors.ink,
      borderColor: colors.ink,
    },
    cardPrimary: {
      backgroundColor: colors.primary,
      borderColor: colors.primary,
    },
    alert: {
      flexDirection: 'row',
      alignItems: 'flex-start',
      gap: 11,
      borderRadius: 16,
      padding: 14,
      borderWidth: 1,
    },
    alertSuccess: { backgroundColor: colors.successSurface, borderColor: colors.success },
    alertError: { backgroundColor: colors.dangerSurface, borderColor: colors.danger },
    alertInfo: { backgroundColor: colors.surfaceAlt, borderColor: colors.border },
    alertWarning: { backgroundColor: colors.warningSurface, borderColor: colors.warning },
    alertText: { flex: 1, fontSize: 14, lineHeight: 20, fontWeight: '600' },
    alertSuccessText: { color: colors.successText },
    alertErrorText: { color: colors.dangerText },
    alertInfoText: { color: colors.text },
    alertWarningText: { color: colors.warningText },
    fieldLabel: {
      fontSize: 13,
      fontWeight: '800',
      color: colors.text,
      marginBottom: 9,
      letterSpacing: 0.1,
    },
    fieldHint: {
      fontSize: 12,
      color: colors.textMuted,
      marginTop: 7,
      lineHeight: 17,
    },
    input: {
      minHeight: 52,
      borderRadius: 14,
      borderWidth: 1,
      borderColor: colors.border,
      backgroundColor: colors.surface,
      color: colors.text,
      paddingHorizontal: 15,
      fontSize: 15,
    },
    inputError: { borderColor: colors.danger },
    pill: {
      alignSelf: 'flex-start',
      flexDirection: 'row',
      alignItems: 'center',
      gap: 5,
      borderRadius: 999,
      paddingHorizontal: 11,
      paddingVertical: 6,
    },
    pillNeutral: { backgroundColor: colors.surfaceAlt },
    pillPrimary: { backgroundColor: colors.primary },
    pillSuccess: { backgroundColor: colors.successSurface },
    pillDanger: { backgroundColor: colors.dangerSurface },
    pillWarning: { backgroundColor: colors.warningSurface },
    pillDark: { backgroundColor: 'rgba(255,255,255,0.13)' },
    pillText: { fontSize: 11, fontWeight: '800', letterSpacing: 0.4 },
    pillTextNeutral: { color: colors.textMuted },
    pillTextPrimary: { color: colors.primaryText },
    pillTextSuccess: { color: colors.successText },
    pillTextDanger: { color: colors.dangerText },
    pillTextWarning: { color: colors.warningText },
    pillTextDark: { color: colors.white },
    iconTile: {
      width: 46,
      height: 46,
      borderRadius: 15,
      alignItems: 'center',
      justifyContent: 'center',
    },
    iconTileNeutral: { backgroundColor: colors.surfaceAlt },
    iconTilePrimary: { backgroundColor: colors.primary },
    iconTileSuccess: { backgroundColor: colors.successSurface },
    iconTileDark: { backgroundColor: 'rgba(255,255,255,0.12)' },
    emptyIcon: {
      width: 62,
      height: 62,
      borderRadius: 20,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: colors.surfaceAlt,
      marginBottom: 16,
    },
    emptyTitle: { fontSize: 19, fontWeight: '800', color: colors.text },
    emptyMessage: {
      marginTop: 8,
      fontSize: 14,
      lineHeight: 21,
      color: colors.textMuted,
      textAlign: 'center',
    },
    emptyAction: { marginTop: 20, alignSelf: 'center' },
    overline: {
      fontSize: 11,
      fontWeight: '900',
      letterSpacing: 1.7,
      color: colors.accent,
      textTransform: 'uppercase',
    },
    heading: {
      marginTop: 7,
      fontSize: 27,
      fontWeight: '900',
      color: colors.text,
      letterSpacing: -0.7,
    },
    headingSubtitle: {
      marginTop: 8,
      fontSize: 14,
      lineHeight: 21,
      color: colors.textMuted,
    },
    sectionTitle: {
      fontSize: 16,
      fontWeight: '800',
      color: colors.text,
    },
    sectionSubtitle: {
      marginTop: 4,
      fontSize: 13,
      lineHeight: 19,
      color: colors.textMuted,
    },
  });
}

export function AppButton({
  label,
  onPress,
  loading = false,
  disabled = false,
  variant = 'primary',
  icon,
  style,
}) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  const variants = {
    primary: {
      container: styles.buttonPrimary,
      text: styles.buttonTextPrimary,
      iconColor: colors.primaryText,
    },
    secondary: {
      container: styles.buttonSecondary,
      text: styles.buttonTextSecondary,
      iconColor: colors.onAccent,
    },
    dark: {
      container: styles.buttonDark,
      text: styles.buttonTextDark,
      iconColor: colors.white,
    },
    danger: {
      container: styles.buttonDanger,
      text: styles.buttonTextDark,
      iconColor: colors.white,
    },
    ghost: {
      container: styles.buttonGhost,
      text: styles.buttonTextGhost,
      iconColor: colors.text,
    },
  };

  const selected = variants[variant] ?? variants.primary;
  const isDisabled = disabled || loading;

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel={label}
      disabled={isDisabled}
      onPress={onPress}
      style={({ pressed }) => [
        styles.button,
        selected.container,
        isDisabled && styles.buttonDisabled,
        pressed && !isDisabled && styles.buttonPressed,
        style,
      ]}
    >
      {loading ? (
        <ActivityIndicator color={selected.iconColor} />
      ) : (
        <>
          {icon ? (
            <MaterialCommunityIcons
              name={icon}
              size={19}
              color={selected.iconColor}
            />
          ) : null}
          <Text style={[styles.buttonText, selected.text]}>{label}</Text>
        </>
      )}
    </Pressable>
  );
}

export function AppCard({ children, style, tone = 'default' }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  const toneStyle =
    tone === 'dark' ? styles.cardDark : tone === 'primary' ? styles.cardPrimary : null;

  return <View style={[styles.card, toneStyle, style]}>{children}</View>;
}

export function Pill({ label, tone = 'neutral', icon, style }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  const tones = {
    neutral: [styles.pillNeutral, styles.pillTextNeutral, colors.textMuted],
    primary: [styles.pillPrimary, styles.pillTextPrimary, colors.primaryText],
    success: [styles.pillSuccess, styles.pillTextSuccess, colors.success],
    danger: [styles.pillDanger, styles.pillTextDanger, colors.danger],
    warning: [styles.pillWarning, styles.pillTextWarning, colors.warning],
    dark: [styles.pillDark, styles.pillTextDark, colors.white],
  };

  const [container, text, iconColor] = tones[tone] ?? tones.neutral;

  return (
    <View style={[styles.pill, container, style]}>
      {icon ? <MaterialCommunityIcons name={icon} size={13} color={iconColor} /> : null}
      <Text style={[styles.pillText, text]}>{label}</Text>
    </View>
  );
}

export function IconTile({ name, size = 22, tone = 'neutral', style, color }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  const tones = {
    neutral: [styles.iconTileNeutral, colors.accent],
    primary: [styles.iconTilePrimary, colors.primaryText],
    success: [styles.iconTileSuccess, colors.success],
    dark: [styles.iconTileDark, colors.white],
  };

  const [container, fallbackColor] = tones[tone] ?? tones.neutral;

  return (
    <View style={[styles.iconTile, container, style]}>
      <MaterialCommunityIcons name={name} size={size} color={color || fallbackColor} />
    </View>
  );
}

const ALERT_ICONS = {
  success: 'check-circle-outline',
  error: 'alert-circle-outline',
  info: 'information-outline',
  warning: 'alert-outline',
};

export function AlertBox({ message, tone = 'error', style }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  if (!message) {
    return null;
  }

  const variants = {
    success: {
      container: styles.alertSuccess,
      text: styles.alertSuccessText,
      iconColor: colors.success,
    },
    error: {
      container: styles.alertError,
      text: styles.alertErrorText,
      iconColor: colors.danger,
    },
    info: {
      container: styles.alertInfo,
      text: styles.alertInfoText,
      iconColor: colors.accent,
    },
    warning: {
      container: styles.alertWarning,
      text: styles.alertWarningText,
      iconColor: colors.warning,
    },
  };

  const selected = variants[tone] ?? variants.error;

  return (
    <View style={[styles.alert, selected.container, style]}>
      <MaterialCommunityIcons
        name={ALERT_ICONS[tone] ?? ALERT_ICONS.error}
        size={19}
        color={selected.iconColor}
      />
      <Text style={[styles.alertText, selected.text]}>{message}</Text>
    </View>
  );
}

export function FormField({
  label,
  hint,
  error,
  containerStyle,
  ...inputProps
}) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  return (
    <View style={containerStyle}>
      <Text style={styles.fieldLabel}>{label}</Text>
      <TextInput
        accessibilityLabel={label}
        autoCapitalize="none"
        autoCorrect={false}
        placeholderTextColor={colors.textMuted}
        style={[styles.input, error ? styles.inputError : null]}
        {...inputProps}
      />
      {error ? <AlertBox message={error} tone="error" style={{ marginTop: 8 }} /> : null}
      {hint && !error ? <Text style={styles.fieldHint}>{hint}</Text> : null}
    </View>
  );
}

export function EmptyState({ icon = 'ticket-outline', title, message, actionLabel, onAction }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  return (
    <View style={{ alignItems: 'center', paddingVertical: 32 }}>
      <View style={styles.emptyIcon}>
        <MaterialCommunityIcons name={icon} size={28} color={colors.accent} />
      </View>
      <Text style={styles.emptyTitle}>{title}</Text>
      {message ? <Text style={styles.emptyMessage}>{message}</Text> : null}
      {actionLabel && onAction ? (
        <AppButton
          label={actionLabel}
          onPress={onAction}
          variant="ghost"
          icon="arrow-right"
          style={styles.emptyAction}
        />
      ) : null}
    </View>
  );
}

export function SectionHeading({ overline, title, subtitle }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  return (
    <View>
      {overline ? <Text style={styles.overline}>{overline}</Text> : null}
      <Text style={styles.heading}>{title}</Text>
      {subtitle ? <Text style={styles.headingSubtitle}>{subtitle}</Text> : null}
    </View>
  );
}

export function PanelHeading({ title, subtitle, right }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  return (
    <View style={{ flexDirection: 'row', alignItems: 'flex-start', gap: 12 }}>
      <View style={{ flex: 1 }}>
        <Text style={styles.sectionTitle}>{title}</Text>
        {subtitle ? <Text style={styles.sectionSubtitle}>{subtitle}</Text> : null}
      </View>
      {right}
    </View>
  );
}
