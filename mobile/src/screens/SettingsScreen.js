import React, { useMemo } from 'react';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';

import { AppButton, AppCard, SectionHeading } from '../components/ui';
import { initials } from '../format';
import { useTheme } from '../theme';

function makeStyles(colors) {
  return StyleSheet.create({
    content: { padding: 20, paddingBottom: 32 },
    accountTop: { flexDirection: 'row', alignItems: 'center', gap: 14 },
    avatar: {
      width: 54,
      height: 54,
      borderRadius: 18,
      backgroundColor: colors.primary,
      alignItems: 'center',
      justifyContent: 'center',
    },
    avatarText: { fontSize: 20, fontWeight: '800', color: colors.primaryText },
    accountBody: { flex: 1 },
    accountName: { fontSize: 17, fontWeight: '800', color: colors.text },
    accountEmail: { fontSize: 13, color: colors.textMuted, marginTop: 4 },
    rolePill: {
      alignSelf: 'flex-start',
      borderRadius: 999,
      backgroundColor: colors.surfaceAlt,
      paddingHorizontal: 10,
      paddingVertical: 4,
      marginTop: 10,
    },
    roleText: { fontSize: 11, fontWeight: '800', color: colors.accent },
    card: { marginTop: 16 },
    infoRow: { flexDirection: 'row', gap: 10, alignItems: 'flex-start' },
    infoText: { flex: 1, fontSize: 13, lineHeight: 19, color: colors.textMuted },
    signOut: { marginTop: 16 },
    titleSpacing: { marginBottom: 18 },
  });
}

export default function SettingsScreen({ account, onSignOut }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  return (
    <ScrollView contentContainerStyle={styles.content}>
      <SectionHeading overline="Workspace" title="Settings" />

      <AppCard style={styles.titleSpacing}>
        <View style={styles.accountTop}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>{initials(account?.name, account?.email)}</Text>
          </View>
          <View style={styles.accountBody}>
            <Text style={styles.accountName}>{account?.name || 'Scanner'}</Text>
            <Text style={styles.accountEmail}>{account?.email || 'No email on file'}</Text>
            <View style={styles.rolePill}>
              <Text style={styles.roleText}>SCANNER ACCOUNT</Text>
            </View>
          </View>
        </View>
      </AppCard>

      <AppCard style={styles.card}>
        <View style={styles.infoRow}>
          <MaterialCommunityIcons name="shield-check-outline" size={19} color={colors.accent} />
          <Text style={styles.infoText}>
            Your session is stored securely on this device. Sign out before handing the phone to
            another staff member.
          </Text>
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
