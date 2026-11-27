import React, { useMemo } from 'react';
import {
  ActivityIndicator,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import { AppButton, AppCard, EmptyState, IconTile, Pill, SectionHeading } from '../components/ui';
import { formatEventDate } from '../format';
import { useTheme } from '../theme';

function makeStyles(colors) {
  return StyleSheet.create({
    content: { padding: 20, paddingBottom: 32 },
    headerRow: {
      flexDirection: 'row',
      alignItems: 'flex-end',
      justifyContent: 'space-between',
      gap: 12,
      marginBottom: 18,
    },
    refreshButton: { minHeight: 42, paddingHorizontal: 14 },
    summary: { marginBottom: 20, paddingVertical: 18 },
    summaryRow: { flexDirection: 'row', alignItems: 'center' },
    summaryItem: { flex: 1, gap: 4 },
    summaryDivider: { width: 1, alignSelf: 'stretch', backgroundColor: 'rgba(21,26,46,0.16)' },
    summaryValue: { fontSize: 25, fontWeight: '900', color: colors.ink, letterSpacing: -0.6 },
    summaryLabel: { fontSize: 11, fontWeight: '800', color: 'rgba(21,26,46,0.62)', letterSpacing: 0.5 },
    list: { gap: 13 },
    card: {
      borderRadius: 20,
      borderWidth: 1,
      borderColor: colors.border,
      backgroundColor: colors.surface,
      overflow: 'hidden',
      shadowColor: colors.ink,
      shadowOpacity: 0.05,
      shadowRadius: 10,
      shadowOffset: { width: 0, height: 3 },
      elevation: 2,
    },
    cardSelected: { borderColor: colors.accent, borderWidth: 2 },
    cardPressed: { opacity: 0.72 },
    accentRail: {
      position: 'absolute',
      left: 0,
      top: 0,
      bottom: 0,
      width: 4,
      backgroundColor: colors.accent,
    },
    cardInner: { padding: 16, paddingLeft: 20 },
    cardTop: { flexDirection: 'row', alignItems: 'flex-start', gap: 12 },
    cardBody: { flex: 1 },
    eventName: { fontSize: 16, fontWeight: '800', color: colors.text },
    eventMeta: { fontSize: 13, color: colors.textMuted, marginTop: 5, lineHeight: 18 },
    cardFooter: {
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'space-between',
      borderTopWidth: 1,
      borderTopColor: colors.border,
      marginTop: 15,
      paddingTop: 13,
    },
    footerLabel: { fontSize: 12, fontWeight: '700', color: colors.textMuted },
    actionRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    actionText: { fontSize: 12, fontWeight: '800', color: colors.accent },
    loading: { paddingVertical: 64, alignItems: 'center' },
    loadingText: { marginTop: 14, fontSize: 14, color: colors.textMuted },
  });
}

export default function EventsScreen({ events, loading, onRefresh, onSelect, selectedEventId }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);

  const totalScans = useMemo(
    () => events.reduce((sum, event) => sum + Number(event.scanTicket ?? 0), 0),
    [events],
  );

  if (loading && events.length === 0) {
    return (
      <View style={styles.loading}>
        <ActivityIndicator color={colors.accent} />
        <Text style={styles.loadingText}>Loading assigned events…</Text>
      </View>
    );
  }

  return (
    <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
      <View style={styles.headerRow}>
        <View style={{ flex: 1 }}>
          <SectionHeading
            overline="Your desk"
            title="Assigned events"
            subtitle="Choose an event to start validating tickets."
          />
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

      <AppCard tone="primary" style={styles.summary}>
        <View style={styles.summaryRow}>
          <View style={styles.summaryItem}>
            <IconTile name="calendar-outline" tone="primary" size={20} />
            <Text style={styles.summaryValue}>{events.length}</Text>
            <Text style={styles.summaryLabel}>EVENTS ASSIGNED</Text>
          </View>
          <View style={styles.summaryDivider} />
          <View style={styles.summaryItem}>
            <IconTile name="check-circle-outline" tone="primary" size={20} />
            <Text style={styles.summaryValue}>{totalScans}</Text>
            <Text style={styles.summaryLabel}>TICKETS CHECKED IN</Text>
          </View>
        </View>
      </AppCard>

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
                <View style={styles.accentRail} />
                <View style={styles.cardInner}>
                  <View style={styles.cardTop}>
                    <IconTile name="ticket-outline" size={21} />
                    <View style={styles.cardBody}>
                      <Text style={styles.eventName}>{event.name || `Event #${event.id}`}</Text>
                      <Text style={styles.eventMeta}>{formatEventDate(event.start_time)}</Text>
                      {event.address ? (
                        <Text style={styles.eventMeta} numberOfLines={1}>
                          {event.address}
                        </Text>
                      ) : null}
                    </View>
                    {selected ? <Pill label="Active" tone="primary" /> : null}
                  </View>
                  <View style={styles.cardFooter}>
                    <Text style={styles.footerLabel}>Tickets scanned</Text>
                    <View style={styles.actionRow}>
                      <Pill
                        label={String(Number(event.scanTicket ?? 0))}
                        tone="success"
                        icon="check"
                      />
                      <Text style={styles.actionText}>Start →</Text>
                    </View>
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
