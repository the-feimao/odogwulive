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

import { AlertBox, AppButton, AppCard, FormField, IconTile, SectionHeading } from '../components/ui';
import { useTheme } from '../theme';

function makeStyles(colors) {
  return StyleSheet.create({
    scroll: { flexGrow: 1, padding: 20, paddingTop: 28, paddingBottom: 28 },
    hero: {
      borderRadius: 24,
      backgroundColor: colors.ink,
      padding: 22,
      marginBottom: 26,
      overflow: 'hidden',
    },
    heroGlow: {
      position: 'absolute',
      width: 180,
      height: 180,
      borderRadius: 90,
      backgroundColor: 'rgba(254, 192, 9, 0.16)',
      top: -90,
      right: -60,
    },
    heroRow: { flexDirection: 'row', alignItems: 'center', gap: 12 },
    heroEyebrow: {
      fontSize: 10,
      fontWeight: '900',
      letterSpacing: 1.8,
      color: colors.primary,
    },
    heroTitle: { fontSize: 25, fontWeight: '900', color: colors.white, marginTop: 4, letterSpacing: -0.5 },
    heroSubtitle: {
      fontSize: 14,
      lineHeight: 21,
      color: 'rgba(255,255,255,0.72)',
      marginTop: 14,
    },
    heroPoints: { gap: 10, marginTop: 18 },
    heroPoint: { flexDirection: 'row', alignItems: 'center', gap: 9 },
    heroPointText: { fontSize: 12, fontWeight: '700', color: 'rgba(255,255,255,0.88)' },
    introBlock: { marginBottom: 20 },
    formGap: { gap: 18 },
    divider: {
      height: 1,
      backgroundColor: colors.border,
      marginVertical: 20,
    },
    footer: {
      marginTop: 22,
      flexDirection: 'row',
      alignItems: 'flex-start',
      gap: 10,
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
      <ScrollView keyboardShouldPersistTaps="handled" contentContainerStyle={styles.scroll}>
        <View style={styles.hero}>
          <View style={styles.heroGlow} />
          <View style={styles.heroRow}>
            <IconTile name="qrcode-scan" tone="primary" size={26} />
            <View>
              <Text style={styles.heroEyebrow}>ODOGWU FEST · GATE CREW</Text>
              <Text style={styles.heroTitle}>Ticket Scanner</Text>
            </View>
          </View>
          <Text style={styles.heroSubtitle}>
            Fast, quiet check-in for assigned events. Scan a guest’s QR code and get an answer
            before the queue moves.
          </Text>
          <View style={styles.heroPoints}>
            <View style={styles.heroPoint}>
              <MaterialCommunityIcons name="check" size={15} color={colors.primary} />
              <Text style={styles.heroPointText}>Only your assigned events appear</Text>
            </View>
            <View style={styles.heroPoint}>
              <MaterialCommunityIcons name="check" size={15} color={colors.primary} />
              <Text style={styles.heroPointText}>Every scan is validated instantly</Text>
            </View>
            <View style={styles.heroPoint}>
              <MaterialCommunityIcons name="check" size={15} color={colors.primary} />
              <Text style={styles.heroPointText}>Session stays on this device</Text>
            </View>
          </View>
        </View>

        <View style={styles.introBlock}>
          <SectionHeading
            overline="Scanner sign in"
            title="Welcome back"
            subtitle="Use the same account you use for the web scanner."
          />
        </View>

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

          <View style={styles.divider} />

          <AppButton
            label="Sign in to scan"
            icon="login"
            onPress={handleSubmit}
            loading={busy}
            disabled={busy}
          />
        </AppCard>

        <View style={styles.footer}>
          <MaterialCommunityIcons name="lock-outline" size={16} color={colors.accent} />
          <Text style={styles.footerText}>
            The API connection is managed by your organization. Ask your organizer if you cannot
            sign in or see an assigned event.
          </Text>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
