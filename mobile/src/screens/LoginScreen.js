import React, { useMemo, useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';

import { AlertBox, AppButton, AppCard, FormField, SectionHeading } from '../components/ui';
import { useTheme } from '../theme';

function makeStyles(colors) {
  return StyleSheet.create({
    scroll: { flexGrow: 1, padding: 20, paddingTop: 36 },
    brandRow: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: 30 },
    brandMark: {
      width: 48,
      height: 48,
      borderRadius: 16,
      backgroundColor: colors.primary,
      alignItems: 'center',
      justifyContent: 'center',
    },
    brandEyebrow: {
      fontSize: 11,
      fontWeight: '800',
      letterSpacing: 1.6,
      color: colors.accent,
    },
    brandName: { fontSize: 18, fontWeight: '800', color: colors.text, marginTop: 2 },
    intro: { fontSize: 15, lineHeight: 22, color: colors.textMuted, marginTop: 10, marginBottom: 22 },
    formGap: { gap: 16 },
    footer: {
      marginTop: 24,
      flexDirection: 'row',
      alignItems: 'flex-start',
      gap: 8,
    },
    footerText: { flex: 1, fontSize: 12, lineHeight: 18, color: colors.textMuted },
  });
}

export default function LoginScreen({ onLogin, busy }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [localError, setLocalError] = useState('');

  const handleSubmit = () => {
    setLocalError('');

    if (!email.trim() || !password) {
      setLocalError('Enter your scanner email and password.');
      return;
    }

    onLogin({ email, password });
  };

  return (
    <KeyboardAvoidingView
      style={{ flex: 1 }}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <ScrollView
        keyboardShouldPersistTaps="handled"
        contentContainerStyle={styles.scroll}
      >
        <View style={styles.brandRow}>
          <View style={styles.brandMark}>
            <MaterialCommunityIcons name="qrcode-scan" size={25} color={colors.primaryText} />
          </View>
          <View>
            <Text style={styles.brandEyebrow}>ODOGWU FEST · STAFF TOOL</Text>
            <Text style={styles.brandName}>Ticket Scanner</Text>
          </View>
        </View>

        <SectionHeading
          overline="Scanner sign in"
          title="Ready to check in guests"
        />
        <Text style={styles.intro}>
          Use the same account you use for the web scanner, then choose an assigned event.
        </Text>

        <AppCard>
          <View style={styles.formGap}>
            <FormField
              label="Email"
              value={email}
              onChangeText={setEmail}
              placeholder="scanner@example.com"
              keyboardType="email-address"
              textContentType="username"
              autoComplete="username"
              returnKeyType="next"
            />
            <FormField
              label="Password"
              value={password}
              onChangeText={setPassword}
              placeholder="••••••••"
              secureTextEntry
              textContentType="password"
              autoComplete="current-password"
              returnKeyType="go"
              onSubmitEditing={handleSubmit}
            />
          </View>

          <AlertBox message={localError} tone="error" style={{ marginTop: 16 }} />

          <AppButton
            label="Sign in"
            icon="login"
            onPress={handleSubmit}
            loading={busy}
            disabled={busy}
            style={{ marginTop: 20 }}
          />
        </AppCard>

        <View style={styles.footer}>
          <MaterialCommunityIcons name="shield-check-outline" size={17} color={colors.accent} />
          <Text style={styles.footerText}>
            Your session is stored securely on this device. Ask your organizer if you cannot see
            an assigned event.
          </Text>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
