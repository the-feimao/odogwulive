import React, { useMemo } from 'react';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';

import { AppButton, AppCard, IconTile, PanelHeading, Pill, SectionHeading } from '../components/ui';
import { initials } from '../format';
import { useTheme } from '../theme';

function makeStyles(colors) {
  return StyleSheet.create({
    content: { padding: 20, paddingBottom: 36 },
    hero: { marginBottom: 16 },
    accountTop: { flexDirection: 'row', alignItems: 'center', gap: 14 },
    avatar: {
      width: 58,
      height: 58,
      borderRadius: 19,
      backgroundColor: colors.primary,
      alignItems: 'center',
      justifyContent: 'center',
    },
    avatarText: { fontSize: 21, fontWeight: '900', color: colors.ink },
    accountBody: { flex: 1 },
    accountName: { fontSize: 18, fontWeight: '900', color: colors.text },
    accountEmail: { fontSize: 13, color: colors.textMuted, marginTop: 4 },
    roleRow: { flexDirection: 'row', gap: 7, marginTop: 10 },
    heroFooter: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 8,
      borderTopWidth: 1,
      borderTopColor: colors.border,
      marginTop: 17,
      paddingTop: 14,
    },
    heroFooterText: { fontSize: 12, lineHeight: 18, color: colors.textMuted },
    card: { marginTop: 14 },
    cardBody: { marginTop: 16 },
    infoRow: { flexDirection: 'row', gap: 12, alignItems: 'flex-start' },
    infoText: { flex: 1, fontSize: 13, lineHeight: 20, color: colors.textMuted },
    steps: { gap: 16, marginTop: 18 },
    step: { flexDirection: 'row', alignItems: 'flex-start', gap: 12 },
    stepText: { flex: 1, fontSize: 13, lineHeight: 20, color: colors.textMuted },
    signOut: { marginTop: 22 },
  });
}

export default function SettingsScreen({ account, onSignOut }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  return (
    <ScrollView contentContainerStyle={styles.content}>
      <SectionHeading
        overline="Workspace"
        title="Settings"
        subtitle="Your scanner profile and device session."
      />

      <AppCard style={[styles.hero, { marginTop: 20 }]}>
        <View style={styles.accountTop}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>{initials(account?.name, account?.email)}</Text>
          </View>
          <View style={styles.accountBody}>
            <Text style={styles.accountName}>{account?.name || 'Scanner'}</Text>
            <Text style={styles.accountEmail}>{account?.email || 'No email on file'}</Text>
            <View style={styles.roleRow}>
              <Pill label="SCANNER" tone="primary" icon="shield-check-outline" />
              <Pill label="ACTIVE SESSION" tone="success" />
            </View>
          </View>
        </View>
        <View style={styles.heroFooter}>
          <MaterialCommunityIcons name="lock-outline" size={15} color={colors.accent} />
          <Text style={styles.heroFooterText}>
            Your token is stored securely on this device. Sign out before handing the phone to
            another staff member.
          </Text>
        </View>
      </AppCard>

      <AppCard style={styles.card}>
        <PanelHeading
          title="Connection"
          subtitle="The API address is configured for this build."
          right={<Pill label="LOCKED" tone="neutral" icon="lock-outline" />}
        />
        <View style={styles.cardBody}>
          <View style={styles.infoRow}>
            <IconTile name="server-outline" size={20} />
            <Text style={styles.infoText}>
              Scanners cannot edit the server address. If the app cannot reach Laravel, ask your
              organizer to check the build configuration or the local network.
            </Text>
          </View>
        </View>
      </AppCard>

      <AppCard style={styles.card}>
        <PanelHeading title="How check-in works" subtitle="Three steps, no guessing." />
        <View style={styles.steps}>
          <View style={styles.step}>
            <IconTile name="calendar-outline" size={18} />
            <Text style={styles.stepText}>Choose one of your assigned events.</Text>
          </View>
          <View style={styles.step}>
            <IconTile name="qrcode-scan" size={18} />
            <Text style={styles.stepText}>Point the camera at the attendee’s QR ticket.</Text>
          </View>
          <View style={styles.step}>
            <IconTile name="check-circle-outline" size={18} tone="success" />
            <Text style={styles.stepText}>Read the result before waving the guest through.</Text>
          </View>
        </View>
      </AppCard>

      <AppButton
        label="Sign out"
        icon="logout"
        variant="danger"
        onPress={onSignOut}
        style={styles.signOut}
      />
    </ScrollView>
  );
}
