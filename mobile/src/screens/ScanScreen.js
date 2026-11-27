import React, { useCallback, useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  useWindowDimensions,
  View,
} from 'react-native';
import { CameraView, useCameraPermissions } from 'expo-camera';
import { MaterialCommunityIcons } from '@expo/vector-icons';

import {
  AppButton,
  AppCard,
  EmptyState,
  FormField,
  IconTile,
  PanelHeading,
  Pill,
} from '../components/ui';
import { formatEventDate } from '../format';
import { useTheme } from '../theme';

const BARCODE_SETTINGS = { barcodeTypes: ['qr'] };

function makeStyles(colors, cameraHeight, frameSize) {
  return StyleSheet.create({
    content: { padding: 20, paddingBottom: 36 },
    hero: {
      borderRadius: 24,
      backgroundColor: colors.ink,
      padding: 20,
      marginBottom: 16,
      overflow: 'hidden',
    },
    heroTop: { flexDirection: 'row', alignItems: 'flex-start', gap: 13 },
    heroBody: { flex: 1 },
    heroOverline: {
      fontSize: 10,
      fontWeight: '900',
      letterSpacing: 1.7,
      color: colors.primary,
    },
    eventName: {
      fontSize: 19,
      fontWeight: '900',
      color: colors.white,
      marginTop: 4,
      letterSpacing: -0.3,
    },
    heroMeta: {
      fontSize: 12,
      lineHeight: 18,
      color: 'rgba(255,255,255,0.68)',
      marginTop: 7,
    },
    heroFooter: {
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'space-between',
      borderTopWidth: 1,
      borderTopColor: 'rgba(255,255,255,0.12)',
      marginTop: 16,
      paddingTop: 14,
    },
    heroFooterText: {
      fontSize: 11,
      fontWeight: '700',
      color: 'rgba(255,255,255,0.58)',
    },
    cameraCard: { padding: 0, overflow: 'hidden', borderRadius: 24, marginBottom: 16 },
    cameraShell: { height: cameraHeight, backgroundColor: 'transparent', position: 'relative' },
    camera: { flex: 1, width: '100%' },
    cameraTop: {
      position: 'absolute',
      top: 14,
      left: 14,
      right: 14,
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'space-between',
      gap: 10,
    },
    hudPill: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 7,
      borderRadius: 999,
      backgroundColor: 'rgba(6, 9, 20, 0.62)',
      paddingHorizontal: 12,
      paddingVertical: 8,
    },
    hudDot: { width: 7, height: 7, borderRadius: 4, backgroundColor: colors.primary },
    hudDotPending: { backgroundColor: colors.white, opacity: 0.6 },
    hudText: { fontSize: 11, fontWeight: '800', color: colors.white },
    cameraErrorOverlay: {
      ...StyleSheet.absoluteFillObject,
      alignItems: 'center',
      justifyContent: 'center',
      gap: 8,
      paddingHorizontal: 20,
      backgroundColor: colors.surface,
    },
    cameraErrorTitle: { fontSize: 15, fontWeight: '900', color: colors.text, textAlign: 'center' },
    cameraErrorText: {
      fontSize: 12,
      lineHeight: 18,
      color: colors.textMuted,
      textAlign: 'center',
    },
    hudActions: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    torchButton: {
      width: 44,
      height: 44,
      borderRadius: 15,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: 'rgba(6, 9, 20, 0.62)',
    },
    overlay: { ...StyleSheet.absoluteFillObject, alignItems: 'center', justifyContent: 'center' },
    scanFrame: {
      width: frameSize,
      height: frameSize,
      borderRadius: 24,
      borderWidth: 1,
      borderColor: 'rgba(255,255,255,0.20)',
    },
    corner: {
      position: 'absolute',
      width: 36,
      height: 36,
      borderColor: colors.primary,
    },
    cornerTopLeft: { top: -2, left: -2, borderTopWidth: 3, borderLeftWidth: 3, borderTopLeftRadius: 24 },
    cornerTopRight: { top: -2, right: -2, borderTopWidth: 3, borderRightWidth: 3, borderTopRightRadius: 24 },
    cornerBottomLeft: { bottom: -2, left: -2, borderBottomWidth: 3, borderLeftWidth: 3, borderBottomLeftRadius: 24 },
    cornerBottomRight: { bottom: -2, right: -2, borderBottomWidth: 3, borderRightWidth: 3, borderBottomRightRadius: 24 },
    scanLine: {
      position: 'absolute',
      top: '50%',
      left: 26,
      right: 26,
      height: 2,
      borderRadius: 2,
      backgroundColor: colors.primary,
      opacity: 0.9,
    },
    scanHint: {
      position: 'absolute',
      bottom: 22,
      left: 18,
      right: 18,
      textAlign: 'center',
      color: colors.white,
      fontSize: 13,
      fontWeight: '700',
      textShadowColor: 'rgba(0,0,0,0.9)',
      textShadowRadius: 5,
    },
    scanSubHint: {
      position: 'absolute',
      bottom: 9,
      left: 18,
      right: 18,
      textAlign: 'center',
      color: 'rgba(255,255,255,0.62)',
      fontSize: 11,
      fontWeight: '600',
    },
    busyOverlay: {
      ...StyleSheet.absoluteFillObject,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: 'rgba(6, 9, 20, 0.72)',
    },
    busyText: { color: colors.white, marginTop: 12, fontSize: 14, fontWeight: '800' },
    loading: { paddingVertical: 48, alignItems: 'center' },
    permissionBox: { alignItems: 'center', paddingVertical: 28, paddingHorizontal: 18 },
    permissionTitle: { fontSize: 18, fontWeight: '900', color: colors.text, textAlign: 'center' },
    permissionText: {
      fontSize: 14,
      lineHeight: 21,
      color: colors.textMuted,
      textAlign: 'center',
      marginTop: 8,
      marginBottom: 20,
    },
    resultBanner: {
      flexDirection: 'row',
      alignItems: 'flex-start',
      gap: 11,
      borderRadius: 16,
      borderWidth: 1,
      paddingVertical: 12,
      paddingHorizontal: 14,
      marginBottom: 14,
    },
    resultBannerSuccess: { backgroundColor: colors.successSurface, borderColor: colors.success },
    resultBannerError: { backgroundColor: colors.dangerSurface, borderColor: colors.danger },
    resultBannerBody: { flex: 1 },
    resultHeadingRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    resultTitle: { fontSize: 14, fontWeight: '900', color: colors.text },
    resultMessage: { fontSize: 13, lineHeight: 18, color: colors.textMuted, marginTop: 3 },
    resultCode: {
      marginTop: 6,
      fontSize: 11,
      fontWeight: '700',
      color: colors.text,
      fontFamily: 'monospace',
    },
    resultTime: { fontSize: 11, color: colors.textMuted, marginTop: 5 },
    resultClose: {
      width: 26,
      height: 26,
      borderRadius: 9,
      alignItems: 'center',
      justifyContent: 'center',
    },
    manualCard: { gap: 16 },
    manualRow: { flexDirection: 'row', alignItems: 'flex-end', gap: 10 },
    manualInput: { flex: 1 },
    manualButton: { minHeight: 52, paddingHorizontal: 18 },
  });
}

const RESULT_VISIBLE_MS = 4500;

export default function ScanScreen({
  event,
  onScan,
  onManual,
  processing,
  result,
  onDismissResult,
  onGoToEvents,
}) {
  const { colors } = useTheme();
  const { width: windowWidth } = useWindowDimensions();

  // Keep the preview at the camera's 4:3 ratio so CameraX scales it to fill
  // the whole view instead of drawing it at native size in the top corner.
  const cameraWidth = Math.max(240, windowWidth - 40);
  const cameraHeight = Math.round(cameraWidth * 0.75);
  const frameSize = Math.max(132, Math.min(cameraWidth - 72, cameraHeight - 96, 220));

  const styles = useMemo(
    () => makeStyles(colors, cameraHeight, frameSize),
    [colors, cameraHeight, frameSize],
  );
  const [permission, requestPermission] = useCameraPermissions();
  const [torch, setTorch] = useState(false);
  const [cameraError, setCameraError] = useState('');
  const [cameraReady, setCameraReady] = useState(false);
  const [cameraKey, setCameraKey] = useState(0);
  const [cameraSize, setCameraSize] = useState(null);
  const [manualCode, setManualCode] = useState('');

  const restartCamera = useCallback(() => {
    setCameraError('');
    setCameraReady(false);
    setCameraKey((current) => current + 1);
  }, []);

  const handleCameraLayout = useCallback((event) => {
    const { width, height } = event.nativeEvent.layout;

    if (width > 0 && height > 0) {
      setCameraSize((current) =>
        current && current.width === width && current.height === height
          ? current
          : { width, height },
      );
    }
  }, []);

  const handleBarcode = useCallback(
    (scanResult) => {
      if (scanResult?.data) {
        onScan(scanResult.data);
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

  const resultTime = useMemo(() => {
    if (!result?.at) {
      return null;
    }

    return new Date(result.at).toLocaleTimeString([], {
      hour: '2-digit',
      minute: '2-digit',
    });
  }, [result]);

  useEffect(() => {
    if (!result || !onDismissResult) {
      return undefined;
    }

    const timer = setTimeout(onDismissResult, RESULT_VISIBLE_MS);

    return () => clearTimeout(timer);
  }, [result, onDismissResult]);

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

  const success = result?.tone === 'success';

  return (
    <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
      <AppCard tone="dark" style={styles.hero}>
        <View style={styles.heroTop}>
          <IconTile name="ticket-outline" tone="dark" size={23} />
          <View style={styles.heroBody}>
            <Text style={styles.heroOverline}>NOW SCANNING</Text>
            <Text style={styles.eventName}>{event.name || `Event #${event.id}`}</Text>
            <Text style={styles.heroMeta}>{formatEventDate(event.start_time)}</Text>
            {event.address ? <Text style={styles.heroMeta}>{event.address}</Text> : null}
          </View>
        </View>
        <View style={styles.heroFooter}>
          <Pill label="Live check-in" tone="primary" icon="radio-button-checked" />
          <Text style={styles.heroFooterText}>One scan per ticket</Text>
        </View>
      </AppCard>

      {result ? (
        <View
          accessibilityRole="alert"
          style={[
            styles.resultBanner,
            success ? styles.resultBannerSuccess : styles.resultBannerError,
          ]}
        >
          <MaterialCommunityIcons
            name={success ? 'check-circle-outline' : 'alert-circle-outline'}
            size={22}
            color={success ? colors.success : colors.danger}
          />
          <View style={styles.resultBannerBody}>
            <View style={styles.resultHeadingRow}>
              <Text style={styles.resultTitle}>
                {success ? 'Ticket accepted' : 'Check failed'}
              </Text>
              <Pill label={success ? 'VALID' : 'REVIEW'} tone={success ? 'success' : 'danger'} />
            </View>
            <Text style={styles.resultMessage}>{result.message}</Text>
            {result.code ? <Text style={styles.resultCode}>Code: {result.code}</Text> : null}
            {resultTime ? <Text style={styles.resultTime}>Checked at {resultTime}</Text> : null}
          </View>
          <Pressable
            accessibilityRole="button"
            accessibilityLabel="Dismiss scan result"
            onPress={onDismissResult}
            style={styles.resultClose}
          >
            <MaterialCommunityIcons name="close" size={16} color={colors.textMuted} />
          </Pressable>
        </View>
      ) : null}

      {!permission ? (
        <AppCard style={styles.cameraCard}>
          <View style={[styles.cameraShell, styles.loading]}>
            <ActivityIndicator color={colors.primary} />
          </View>
        </AppCard>
      ) : !permission.granted ? (
        <AppCard style={styles.cameraCard}>
          <View style={styles.permissionBox}>
            <IconTile name="camera-outline" size={28} />
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
          <View style={styles.cameraShell} onLayout={handleCameraLayout}>
            <CameraView
              key={cameraKey}
              testID="scanner-camera-view"
              style={
                cameraSize
                  ? { width: cameraSize.width, height: cameraSize.height }
                  : styles.camera
              }
              facing="back"
              ratio="4:3"
              active={!processing && !cameraError}
              enableTorch={torch}
              barcodeScannerSettings={BARCODE_SETTINGS}
              onBarcodeScanned={processing ? undefined : handleBarcode}
              onCameraReady={() => {
                setCameraReady(true);
                setCameraError('');
              }}
              onMountError={(error) => {
                setCameraReady(false);
                setCameraError(error?.message || 'The camera could not be started.');
              }}
            />
            <View style={styles.cameraTop} pointerEvents="box-none">
              <View style={styles.hudPill}>
                <View style={[styles.hudDot, !cameraReady && styles.hudDotPending]} />
                <Text style={styles.hudText}>
                  {cameraError
                    ? 'CAMERA UNAVAILABLE'
                    : cameraReady
                      ? 'SCANNER READY'
                      : 'STARTING CAMERA'}
                </Text>
              </View>
              <View style={styles.hudActions}>
                <Pressable
                  accessibilityRole="button"
                  accessibilityLabel="Restart camera preview"
                  onPress={restartCamera}
                  style={styles.torchButton}
                >
                  <MaterialCommunityIcons name="refresh" size={20} color={colors.white} />
                </Pressable>
                <Pressable
                  accessibilityRole="button"
                  accessibilityLabel={torch ? 'Turn torch off' : 'Turn torch on'}
                  onPress={() => setTorch((current) => !current)}
                  style={styles.torchButton}
                >
                  <MaterialCommunityIcons
                    name={torch ? 'flashlight-off' : 'flashlight'}
                    size={21}
                    color={colors.white}
                  />
                </Pressable>
              </View>
            </View>
            {cameraReady && !cameraError ? (
              <View style={styles.overlay} pointerEvents="box-none">
                <View style={styles.scanFrame}>
                  <View style={[styles.corner, styles.cornerTopLeft]} />
                  <View style={[styles.corner, styles.cornerTopRight]} />
                  <View style={[styles.corner, styles.cornerBottomLeft]} />
                  <View style={[styles.corner, styles.cornerBottomRight]} />
                  <View style={styles.scanLine} />
                </View>
                <Text style={styles.scanHint}>Hold the attendee QR code inside the frame</Text>
                <Text style={styles.scanSubHint}>Validation starts automatically</Text>
              </View>
            ) : null}
            {cameraError ? (
              <View style={styles.cameraErrorOverlay}>
                <MaterialCommunityIcons
                  name="camera-off-outline"
                  size={28}
                  color={colors.danger}
                />
                <Text style={styles.cameraErrorTitle}>Camera unavailable</Text>
                <Text style={styles.cameraErrorText}>{cameraError}</Text>
                <AppButton
                  label="Try again"
                  icon="refresh"
                  variant="ghost"
                  onPress={restartCamera}
                />
              </View>
            ) : null}
            {processing ? (
              <View style={styles.busyOverlay}>
                <ActivityIndicator color={colors.primary} size="large" />
                <Text style={styles.busyText}>Validating ticket…</Text>
              </View>
            ) : null}
          </View>
        </AppCard>
      )}

      <AppCard>
        <View style={styles.manualCard}>
          <PanelHeading
            title="Manual entry"
            subtitle="Use this when the QR code is damaged or the camera is unavailable."
            right={<Pill label="BACKUP" tone="neutral" icon="keyboard-outline" />}
          />
          <View style={styles.manualRow}>
            <FormField
              containerStyle={styles.manualInput}
              label="Ticket code"
              value={manualCode}
              onChangeText={setManualCode}
              placeholder="Enter the ticket number"
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
        </View>
      </AppCard>
    </ScrollView>
  );
}
