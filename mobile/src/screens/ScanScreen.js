import React, { useCallback, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { CameraView, useCameraPermissions } from 'expo-camera';
import { MaterialCommunityIcons } from '@expo/vector-icons';

import { AppButton, AppCard, EmptyState, FormField } from '../components/ui';
import { formatEventDate } from '../format';
import { useTheme } from '../theme';

const BARCODE_SETTINGS = { barcodeTypes: ['qr'] };

function makeStyles(colors) {
  return StyleSheet.create({
    content: { padding: 20, paddingBottom: 32 },
    eventCard: { marginBottom: 16 },
    eventTop: { flexDirection: 'row', alignItems: 'flex-start', gap: 12 },
    eventIcon: {
      width: 44,
      height: 44,
      borderRadius: 14,
      backgroundColor: colors.surfaceAlt,
      alignItems: 'center',
      justifyContent: 'center',
    },
    eventBody: { flex: 1 },
    overline: {
      fontSize: 11,
      fontWeight: '800',
      letterSpacing: 1.4,
      color: colors.accent,
      textTransform: 'uppercase',
    },
    eventName: { fontSize: 18, fontWeight: '800', color: colors.text, marginTop: 4 },
    eventMeta: { fontSize: 13, color: colors.textMuted, marginTop: 5, lineHeight: 18 },
    cameraCard: { padding: 0, overflow: 'hidden', marginBottom: 16 },
    cameraShell: { height: 330, backgroundColor: '#0D1120' },
    camera: { flex: 1 },
    overlay: { ...StyleSheet.absoluteFillObject, alignItems: 'center', justifyContent: 'center' },
    scanFrame: {
      width: 248,
      height: 188,
      borderWidth: 2,
      borderColor: 'rgba(254, 192, 9, 0.95)',
      borderRadius: 18,
      borderStyle: 'dashed',
    },
    scanHint: {
      position: 'absolute',
      bottom: 18,
      left: 18,
      right: 18,
      textAlign: 'center',
      color: '#FFFFFF',
      fontSize: 13,
      fontWeight: '600',
      textShadowColor: 'rgba(0,0,0,0.8)',
      textShadowRadius: 4,
    },
    torchButton: {
      position: 'absolute',
      top: 14,
      right: 14,
      width: 42,
      height: 42,
      borderRadius: 14,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: 'rgba(13, 17, 32, 0.72)',
    },
    busyOverlay: {
      ...StyleSheet.absoluteFillObject,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: 'rgba(13, 17, 32, 0.55)',
    },
    busyText: { color: '#FFFFFF', marginTop: 10, fontSize: 13, fontWeight: '700' },
    permissionBox: { alignItems: 'center', paddingVertical: 24, paddingHorizontal: 16 },
    permissionIcon: {
      width: 58,
      height: 58,
      borderRadius: 18,
      backgroundColor: colors.surfaceAlt,
      alignItems: 'center',
      justifyContent: 'center',
      marginBottom: 14,
    },
    permissionTitle: { fontSize: 17, fontWeight: '800', color: colors.text, textAlign: 'center' },
    permissionText: {
      fontSize: 14,
      lineHeight: 20,
      color: colors.textMuted,
      textAlign: 'center',
      marginTop: 6,
      marginBottom: 18,
    },
    manualTitle: { fontSize: 16, fontWeight: '800', color: colors.text, marginBottom: 14 },
    manualRow: { flexDirection: 'row', alignItems: 'flex-end', gap: 10 },
    manualInput: { flex: 1 },
    manualButton: { minHeight: 48, paddingHorizontal: 18 },
    loading: { paddingVertical: 36, alignItems: 'center' },
  });
}

export default function ScanScreen({ event, onScan, onManual, processing, onGoToEvents }) {
  const { colors } = useTheme();
  const styles = useMemo(() => makeStyles(colors), [colors]);
  const [permission, requestPermission] = useCameraPermissions();
  const [torch, setTorch] = useState(false);
  const [cameraError, setCameraError] = useState('');
  const [manualCode, setManualCode] = useState('');

  const handleBarcode = useCallback(
    (result) => {
      if (result?.data) {
        onScan(result.data);
      }
    },
    [onScan],
  );

  const handleManual = () => {
    const code = manualCode.trim();

    if (!code) {
      return;
    }

    onManual(code);
    setManualCode('');
  };

  if (!event) {
    return (
      <ScrollView contentContainerStyle={styles.content}>
        <EmptyState
          icon="qrcode-scan"
          title="No event selected"
          message="Choose an assigned event before scanning attendee QR codes."
          actionLabel="Go to events"
          onAction={onGoToEvents}
        />
      </ScrollView>
    );
  }

  return (
    <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
      <AppCard style={styles.eventCard}>
        <View style={styles.eventTop}>
          <View style={styles.eventIcon}>
            <MaterialCommunityIcons name="ticket-outline" size={22} color={colors.accent} />
          </View>
          <View style={styles.eventBody}>
            <Text style={styles.overline}>Scanning for</Text>
            <Text style={styles.eventName}>{event.name || `Event #${event.id}`}</Text>
            <Text style={styles.eventMeta}>{formatEventDate(event.start_time)}</Text>
            {event.address ? <Text style={styles.eventMeta}>{event.address}</Text> : null}
          </View>
        </View>
      </AppCard>

      {cameraError ? (
        <AppCard style={{ marginBottom: 16 }}>
          <Text style={{ color: colors.danger, fontWeight: '700' }}>{cameraError}</Text>
        </AppCard>
      ) : null}

      {!permission ? (
        <AppCard style={styles.cameraCard}>
          <View style={styles.loading}>
            <ActivityIndicator color={colors.accent} />
          </View>
        </AppCard>
      ) : !permission.granted ? (
        <AppCard style={styles.cameraCard}>
          <View style={styles.permissionBox}>
            <View style={styles.permissionIcon}>
              <MaterialCommunityIcons name="camera-outline" size={27} color={colors.accent} />
            </View>
            <Text style={styles.permissionTitle}>Camera access needed</Text>
            <Text style={styles.permissionText}>
              Allow camera access to scan QR tickets. You can still type a code manually below.
            </Text>
            <AppButton
              label={permission.canAskAgain ? 'Allow camera' : 'Open device settings'}
              icon="camera"
              onPress={requestPermission}
            />
          </View>
        </AppCard>
      ) : (
        <AppCard style={styles.cameraCard}>
          <View style={styles.cameraShell}>
            <CameraView
              style={styles.camera}
              facing="back"
              active={!processing}
              enableTorch={torch}
              barcodeScannerSettings={BARCODE_SETTINGS}
              onBarcodeScanned={processing ? undefined : handleBarcode}
              onMountError={(error) =>
                setCameraError(error?.message || 'The camera could not be started.')
              }
            />
            <View style={styles.overlay} pointerEvents="box-none">
              <View style={styles.scanFrame} />
              <Text style={styles.scanHint}>Hold the attendee QR code inside the frame</Text>
              <Pressable
                accessibilityRole="button"
                accessibilityLabel={torch ? 'Turn torch off' : 'Turn torch on'}
                onPress={() => setTorch((current) => !current)}
                style={styles.torchButton}
              >
                <MaterialCommunityIcons
                  name={torch ? 'flashlight-off' : 'flashlight'}
                  size={20}
                  color="#FFFFFF"
                />
              </Pressable>
            </View>
            {processing ? (
              <View style={styles.busyOverlay}>
                <ActivityIndicator color={colors.primary} />
                <Text style={styles.busyText}>Validating ticket…</Text>
              </View>
            ) : null}
          </View>
        </AppCard>
      )}

      <AppCard>
        <Text style={styles.manualTitle}>Manual ticket code</Text>
        <View style={styles.manualRow}>
          <FormField
            containerStyle={styles.manualInput}
            label="Code"
            value={manualCode}
            onChangeText={setManualCode}
            placeholder="Enter code if the camera is unavailable"
            returnKeyType="go"
            onSubmitEditing={handleManual}
          />
          <AppButton
            label="Validate"
            icon="check"
            onPress={handleManual}
            disabled={processing || !manualCode.trim()}
            style={styles.manualButton}
          />
        </View>
      </AppCard>
    </ScrollView>
  );
}
