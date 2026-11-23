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
      minHeight: 48,
      borderRadius: 14,
      paddingHorizontal: 18,
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'center',
      gap: 8,
    },
    buttonPrimary: { backgroundColor: colors.primary },
    buttonSecondary: { backgroundColor: colors.accent },
    buttonDanger: { backgroundColor: colors.danger },
    buttonGhost: {
      backgroundColor: colors.surface,
      borderWidth: 1,
      borderColor: colors.border,
    },
    buttonPressed: { opacity: 0.78 },
    buttonDisabled: { opacity: 0.5 },
    buttonText: { fontSize: 15, fontWeight: '700' },
    buttonPrimaryText: { color: colors.primaryText },
    buttonSecondaryText: { color: colors.onAccent },
    buttonDangerText: { color: colors.white },
    buttonGhostText: { color: colors.text },
    card: {
      backgroundColor: colors.surface,
      borderRadius: 18,
      borderWidth: 1,
      borderColor: colors.border,
      padding: 18,
    },
    alert: {
      flexDirection: 'row',
      alignItems: 'flex-start',
      gap: 10,
      borderRadius: 14,
      padding: 14,
      borderWidth: 1,
    },
    alertSuccess: {
      backgroundColor: '#EDF7F6',
      borderColor: '#A9D6D1',
    },
    alertError: {
      backgroundColor: '#FEF2F4',
      borderColor: '#FCE0E5',
    },
    alertInfo: {
      backgroundColor: colors.surfaceAlt,
      borderColor: colors.border,
    },
    alertWarning: {
      backgroundColor: '#FFF8E6',
      borderColor: '#FFEDBF',
    },
    alertText: { flex: 1, fontSize: 14, lineHeight: 20, fontWeight: '600' },
    alertSuccessText: { color: '#12504D' },
    alertErrorText: { color: '#811E2D' },
    alertInfoText: { color: colors.text },
    alertWarningText: { color: '#6F4200' },
    fieldLabel: {
      fontSize: 13,
      fontWeight: '700',
      color: colors.text,
      marginBottom: 8,
    },
    fieldHint: {
      fontSize: 12,
      color: colors.textMuted,
      marginTop: 6,
      lineHeight: 17,
    },
    input: {
      minHeight: 48,
      borderRadius: 13,
      borderWidth: 1,
      borderColor: colors.border,
      backgroundColor: colors.surface,
      color: colors.text,
      paddingHorizontal: 14,
      fontSize: 15,
    },
    inputError: { borderColor: colors.danger },
    emptyIcon: {
      width: 56,
      height: 56,
      borderRadius: 18,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: colors.surfaceAlt,
      marginBottom: 14,
    },
    emptyTitle: { fontSize: 18, fontWeight: '800', color: colors.text },
    emptyMessage: {
      marginTop: 6,
      fontSize: 14,
      lineHeight: 20,
      color: colors.textMuted,
      textAlign: 'center',
    },
    emptyAction: { marginTop: 18, alignSelf: 'center' },
    overline: {
      fontSize: 11,
      fontWeight: '800',
      letterSpacing: 1.5,
      color: colors.accent,
      textTransform: 'uppercase',
    },
    heading: {
      marginTop: 6,
      fontSize: 24,
      fontWeight: '800',
      color: colors.text,
      letterSpacing: -0.4,
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
      text: styles.buttonPrimaryText,
      iconColor: colors.primaryText,
    },
    secondary: {
      container: styles.buttonSecondary,
      text: styles.buttonSecondaryText,
      iconColor: colors.onAccent,
    },
    danger: {
      container: styles.buttonDanger,
      text: styles.buttonDangerText,
      iconColor: colors.white,
    },
    ghost: {
      container: styles.buttonGhost,
      text: styles.buttonGhostText,
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
              size={18}
              color={selected.iconColor}
            />
          ) : null}
          <Text style={[styles.buttonText, selected.text]}>{label}</Text>
        </>
      )}
    </Pressable>
  );
}

export function AppCard({ children, style }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  return <View style={[styles.card, style]}>{children}</View>;
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
        <MaterialCommunityIcons name={icon} size={26} color={colors.accent} />
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

export function SectionHeading({ overline, title }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  return (
    <View>
      {overline ? <Text style={styles.overline}>{overline}</Text> : null}
      <Text style={styles.heading}>{title}</Text>
    </View>
  );
}
