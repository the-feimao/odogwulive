import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  SafeAreaView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { MaterialCommunityIcons } from '@expo/vector-icons';

import {
  ApiError,
  fetchEvents,
  login,
  resolveApiBaseUrl,
  scanTicket,
} from './src/api';
import { storage } from './src/storage';
import { useTheme } from './src/theme';
import { AlertBox } from './src/components/ui';
import LoginScreen from './src/screens/LoginScreen';
import EventsScreen from './src/screens/EventsScreen';
import ScanScreen from './src/screens/ScanScreen';
import SettingsScreen from './src/screens/SettingsScreen';

const TABS = [
  { key: 'events', label: 'Events', icon: 'calendar-outline' },
  { key: 'scan', label: 'Scan', icon: 'qrcode-scan' },
  { key: 'settings', label: 'Settings', icon: 'cog-outline' },
];

function makeStyles(colors) {
  return StyleSheet.create({
    root: { flex: 1, backgroundColor: colors.background },
    bootRoot: {
      flex: 1,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: colors.background,
    },
    bootText: { marginTop: 14, fontSize: 14, color: colors.textMuted, fontWeight: '600' },
    noticeWrap: { paddingHorizontal: 20, paddingTop: 12 },
    header: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 12,
      paddingHorizontal: 20,
      paddingTop: 18,
      paddingBottom: 14,
    },
    brandMark: {
      width: 44,
      height: 44,
      borderRadius: 15,
      backgroundColor: colors.primary,
      alignItems: 'center',
      justifyContent: 'center',
    },
    headerBody: { flex: 1 },
    headerEyebrow: {
      fontSize: 10,
      fontWeight: '800',
      letterSpacing: 1.5,
      color: colors.accent,
    },
    headerTitle: { fontSize: 18, fontWeight: '800', color: colors.text, marginTop: 2 },
    headerSub: { fontSize: 12, color: colors.textMuted, marginTop: 2 },
    signOutButton: {
      width: 40,
      height: 40,
      borderRadius: 13,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: colors.surface,
      borderWidth: 1,
      borderColor: colors.border,
    },
    workspace: { flex: 1 },
    nav: {
      flexDirection: 'row',
      gap: 8,
      paddingHorizontal: 20,
      paddingTop: 10,
      paddingBottom: 16,
      borderTopWidth: 1,
      borderTopColor: colors.border,
      backgroundColor: colors.surface,
    },
    navItem: {
      flex: 1,
      alignItems: 'center',
      justifyContent: 'center',
      gap: 4,
      minHeight: 56,
      borderRadius: 14,
    },
    navItemActive: { backgroundColor: colors.surfaceAlt },
    navText: { fontSize: 11, fontWeight: '700', color: colors.textMuted },
    navTextActive: { color: colors.accent, fontWeight: '800' },
  });
}

export default function App() {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);
  const baseUrl = useMemo(() => resolveApiBaseUrl(), []);

  const [booting, setBooting] = useState(true);
  const [token, setToken] = useState(null);
  const [account, setAccount] = useState(null);
  const [events, setEvents] = useState([]);
  const [eventsLoading, setEventsLoading] = useState(false);
  const [activeTab, setActiveTab] = useState('events');
  const [selectedEvent, setSelectedEvent] = useState(null);
  const [notice, setNotice] = useState(null);
  const [authBusy, setAuthBusy] = useState(false);
  const [processing, setProcessing] = useState(false);

  const lastScanRef = useRef(0);
  const validatingRef = useRef(false);

  const clearSession = useCallback(async (message) => {
    try {
      await storage.removeToken();
    } catch (_error) {
      // The in-memory session is cleared even if secure storage is unavailable.
    }

    setToken(null);
    setAccount(null);
    setEvents([]);
    setSelectedEvent(null);
    setActiveTab('events');

    if (message) {
      setNotice({ tone: 'error', message });
    }
  }, []);

  const loadEvents = useCallback(
    async (activeToken, activeBaseUrl) => {
      setEventsLoading(true);

      try {
        const payload = await fetchEvents({ token: activeToken, baseUrl: activeBaseUrl });

        if (!payload.success) {
          throw new Error(payload.msg || 'Unable to load assigned events.');
        }

        setEvents(Array.isArray(payload.data) ? payload.data : []);
        return true;
      } catch (error) {
        if (error instanceof ApiError && error.code === 'UNAUTHENTICATED') {
          await clearSession(error.message);
        } else {
          setNotice({
            tone: 'error',
            message: error?.message || 'Unable to load assigned events.',
          });
        }

        return false;
      } finally {
        setEventsLoading(false);
      }
    },
    [clearSession],
  );

  useEffect(() => {
    let active = true;

    (async () => {
      try {
        const savedToken = await storage.getToken();

        if (!active) {
          return;
        }

        if (savedToken) {
          setToken(savedToken);
          await loadEvents(savedToken, baseUrl);
        }
      } catch (_error) {
        if (active) {
          setNotice({ tone: 'error', message: 'Could not load saved settings.' });
        }
      } finally {
        if (active) {
          setBooting(false);
        }
      }
    })();

    return () => {
      active = false;
    };
  }, [loadEvents, baseUrl]);

  const handleLogin = useCallback(
    async ({ email, password }) => {
      setNotice(null);
      setAuthBusy(true);

      try {
        const payload = await login({ email, password, baseUrl });

        if (!payload.success || !payload.data?.token) {
          throw new Error(payload.msg || 'Scanner sign in failed.');
        }

        await storage.setToken(payload.data.token);
        setToken(payload.data.token);
        setAccount(payload.data);
        setActiveTab('events');
        setNotice({ tone: 'success', message: 'Signed in successfully.' });
        await loadEvents(payload.data.token, baseUrl);
      } catch (error) {
        setNotice({ tone: 'error', message: error?.message || 'Scanner sign in failed.' });
      } finally {
        setAuthBusy(false);
      }
    },
    [baseUrl, loadEvents],
  );

  const handleSignOut = useCallback(async () => {
    await clearSession();
    setNotice({ tone: 'info', message: 'Signed out. The stored token was removed.' });
  }, [clearSession]);

  const handleSelectEvent = useCallback((event) => {
    setSelectedEvent(event);
    setActiveTab('scan');
    setNotice(null);
  }, []);

  const handleValidate = useCallback(
    async (rawCode) => {
      const code = String(rawCode ?? '').trim();

      if (!code || !selectedEvent) {
        setNotice({
          tone: 'error',
          message: 'Select an event and provide a ticket code first.',
        });
        return;
      }

      if (validatingRef.current) {
        return;
      }

      validatingRef.current = true;
      setProcessing(true);
      setNotice(null);

      try {
        const payload = await scanTicket({
          code,
          eventId: selectedEvent.id,
          token,
          baseUrl,
        });

        setNotice({
          tone: payload.success ? 'success' : 'error',
          message:
            payload.msg ||
            (payload.success ? 'Ticket validated.' : 'Ticket could not be validated.'),
        });

        if (payload.success) {
          await loadEvents(token, baseUrl);
        }
      } catch (error) {
        if (error instanceof ApiError && error.code === 'UNAUTHENTICATED') {
          await clearSession(error.message);
        } else {
          setNotice({
            tone: 'error',
            message: error?.message || 'Ticket validation failed.',
          });
        }
      } finally {
        validatingRef.current = false;
        setProcessing(false);
      }
    },
    [selectedEvent, token, baseUrl, clearSession, loadEvents],
  );

  const handleScan = useCallback(
    (data) => {
      const now = Date.now();

      if (now - lastScanRef.current < 1800) {
        return;
      }

      lastScanRef.current = now;
      void handleValidate(data);
    },
    [handleValidate],
  );

  if (booting) {
    return (
      <View style={styles.bootRoot}>
        <StatusBar style={colors.statusBar} />
        <ActivityIndicator color={colors.primary} />
        <Text style={styles.bootText}>Preparing scanner…</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.root}>
      <StatusBar style={colors.statusBar} />

      {notice ? (
        <View style={styles.noticeWrap}>
          <AlertBox message={notice.message} tone={notice.tone} />
        </View>
      ) : null}

      {!token ? (
        <LoginScreen onLogin={handleLogin} busy={authBusy} />
      ) : (
        <>
          <View style={styles.header}>
            <View style={styles.brandMark}>
              <MaterialCommunityIcons name="qrcode-scan" size={23} color={colors.primaryText} />
            </View>
            <View style={styles.headerBody}>
              <Text style={styles.headerEyebrow}>ODOGWU FEST · STAFF TOOL</Text>
              <Text style={styles.headerTitle}>Ticket scanner</Text>
              <Text style={styles.headerSub} numberOfLines={1}>
                {account?.name || 'Signed in'}
              </Text>
            </View>
            <Pressable
              accessibilityRole="button"
              accessibilityLabel="Sign out"
              onPress={handleSignOut}
              style={styles.signOutButton}
            >
              <MaterialCommunityIcons name="logout" size={19} color={colors.textMuted} />
            </Pressable>
          </View>

          <View style={styles.workspace}>
            {activeTab === 'events' ? (
              <EventsScreen
                events={events}
                loading={eventsLoading}
                selectedEventId={selectedEvent?.id}
                onRefresh={() => loadEvents(token, baseUrl)}
                onSelect={handleSelectEvent}
              />
            ) : null}

            {activeTab === 'scan' ? (
              <ScanScreen
                event={selectedEvent}
                processing={processing}
                onScan={handleScan}
                onManual={handleValidate}
                onGoToEvents={() => setActiveTab('events')}
              />
            ) : null}

            {activeTab === 'settings' ? (
              <SettingsScreen account={account} onSignOut={handleSignOut} />
            ) : null}
          </View>

          <View style={styles.nav}>
            {TABS.map((tab) => {
              const active = activeTab === tab.key;

              return (
                <Pressable
                  key={tab.key}
                  accessibilityRole="tab"
                  accessibilityState={{ selected: active }}
                  onPress={() => setActiveTab(tab.key)}
                  style={[styles.navItem, active && styles.navItemActive]}
                >
                  <MaterialCommunityIcons
                    name={tab.icon}
                    size={20}
                    color={active ? colors.accent : colors.textMuted}
                  />
                  <Text style={[styles.navText, active && styles.navTextActive]}>{tab.label}</Text>
                </Pressable>
              );
            })}
          </View>
        </>
      )}
    </SafeAreaView>
  );
}
