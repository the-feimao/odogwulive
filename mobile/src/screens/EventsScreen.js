import React, { useMemo } from 'react';
import {
  ActivityIndicator,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';

import { AppButton, EmptyState } from '../components/ui';
import { formatEventDate } from '../format';
import { useTheme } from '../theme';

function makeStyles(colors) {
  return StyleSheet.create({
    content: { padding: 20, paddingBottom: 32 },
    headerRow: {
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'space-between',
      gap: 12,
      marginBottom: 18,
    },
    title: { fontSize: 22, fontWeight: '800', color: colors.text },
    subtitle: { fontSize: 13, color: colors.textMuted, marginTop: 4 },
    refreshButton: { minHeight: 40, paddingHorizontal: 14 },
    list: { gap: 12 },
    card: {
      borderRadius: 16,
      borderWidth: 1,
      borderColor: colors.border,
      backgroundColor: colors.surface,
      padding: 16,
    },
    cardSelected: { borderColor: colors.accent, borderWidth: 2 },
    cardPressed: { opacity: 0.72 },
    cardTop: { flexDirection: 'row', alignItems: 'flex-start', gap: 12 },
    iconBox: {
      width: 42,
      height: 42,
      borderRadius: 13,
      backgroundColor: colors.surfaceAlt,
      alignItems: 'center',
      justifyContent: 'center',
    },
    cardBody: { flex: 1 },
    eventName: { fontSize: 16, fontWeight: '800', color: colors.text },
    eventMeta: { fontSize: 13, color: colors.textMuted, marginTop: 5, lineHeight: 18 },
    cardFooter: {
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'space-between',
      borderTopWidth: 1,
      borderTopColor: colors.border,
      marginTop: 14,
      paddingTop: 12,
    },
    footerLabel: { fontSize: 12, fontWeight: '700', color: colors.textMuted },
    countPill: {
      borderRadius: 999,
      backgroundColor: colors.surfaceAlt,
      paddingHorizontal: 10,
      paddingVertical: 4,
    },
    countText: { fontSize: 12, fontWeight: '800', color: colors.text },
    loading: { paddingVertical: 48, alignItems: 'center' },
  });
}

export default function EventsScreen({ events, loading, onRefresh, onSelect, selectedEventId }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  if (loading && events.length === 0) {
    return (
      <View style={styles.loading}>
        <ActivityIndicator color={colors.accent} />
        <Text style={[styles.eventMeta, { marginTop: 12 }]}>Loading assigned events…</Text>
      </View>
    );
  }

  return (
    <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
      <View style={styles.headerRow}>
        <View style={{ flex: 1 }}>
          <Text style={styles.title}>Assigned events</Text>
          <Text style={styles.subtitle}>Choose an event to start validating tickets.</Text>
        </View>
        <AppButton
          label="Refresh"
          icon="refresh"
          variant="ghost"
          onPress={onRefresh}
          loading={loading}
          disabled={loading}
          style={styles.refreshButton}
        />
      </View>

      {events.length === 0 ? (
        <EmptyState
          icon="calendar-blank-outline"
          title="No assigned events"
          message="No published events are assigned to this scanner yet. Ask an organizer to assign you, then refresh."
          actionLabel={loading ? undefined : 'Refresh again'}
          onAction={onRefresh}
        />
      ) : (
        <View style={styles.list}>
          {events.map((event) => {
            const selected = selectedEventId === event.id;

            return (
              <Pressable
                key={event.id}
                accessibilityRole="button"
                accessibilityLabel={`Scan tickets for ${event.name || `event ${event.id}`}`}
                onPress={() => onSelect(event)}
                style={({ pressed }) => [
                  styles.card,
                  selected && styles.cardSelected,
                  pressed && styles.cardPressed,
                ]}
              >
                <View style={styles.cardTop}>
                  <View style={styles.iconBox}>
                    <MaterialCommunityIcons name="calendar-outline" size={21} color={colors.accent} />
                  </View>
                  <View style={styles.cardBody}>
                    <Text style={styles.eventName}>{event.name || `Event #${event.id}`}</Text>
                    <Text style={styles.eventMeta}>{formatEventDate(event.start_time)}</Text>
                    {event.address ? (
                      <Text style={styles.eventMeta} numberOfLines={1}>
                        {event.address}
                      </Text>
                    ) : null}
                  </View>
                  <MaterialCommunityIcons name="chevron-right" size={20} color={colors.textMuted} />
                </View>
                <View style={styles.cardFooter}>
                  <Text style={styles.footerLabel}>Tickets scanned</Text>
                  <View style={styles.countPill}>
                    <Text style={styles.countText}>{Number(event.scanTicket ?? 0)}</Text>
                  </View>
                </View>
              </Pressable>
            );
          })}
        </View>
      )}
    </ScrollView>
  );
}
