# Odogwu Scanner (Expo Go)

A small, free Expo Go app for validating event tickets against the existing Laravel scanner API. It does **not** add, rename or change any Laravel routes or API contracts.

## Stack

- Expo SDK 58, React Native and React (all pinned to Expo's compatible versions)
- `expo-camera` `CameraView` with QR detection — included in Expo Go
- `expo-secure-store` for the Passport token
- No paid services, no EAS account required, no development build required

## Run it

### 1. Start Laravel on your machine

```bash
cd ..
php artisan serve --host 0.0.0.0 --port=8000
```

The host flag matters: Expo Go on a phone cannot reach `127.0.0.1` on your computer.

### 2. Install the app dependencies

```bash
cd mobile
npm install
npx expo install --check
```

### 3. API address (automatic)

There is no server-address field in the app. It reads the Expo dev server host from `expo-constants` and calls Laravel on port `8000` automatically.

Keep the phone and computer on the same Wi-Fi network and allow port 8000 through the firewall. For a non-standard setup, copy `.env.example` to `.env` and set `EXPO_PUBLIC_API_BASE_URL` to an explicit API URL.

### 4. Start Expo Go

```bash
npm start
```

Install **Expo Go** on your phone, update it to SDK 58 support, and scan the QR code shown in the terminal. On Android, use the Expo Go app's QR scanner; on iOS, use the Camera app.

## Sign in and scan

1. Sign in with the same email/password used by the web scanner at `/scanner-app`.
2. Choose an assigned event.
3. Point the camera at an attendee QR code, or type the ticket code manually.
4. Success and failure messages come straight from the API (`msg` and `success`).

## API endpoints used

No backend changes were needed. The app uses exactly:

| Method | Endpoint | Purpose |
|---|---|---|
| `POST` | `/api/scanner/login` | Email/password login; reads `data.token` |
| `GET` | `/api/scanner/events` | Assigned events and scanned counts |
| `GET` | `/api/scanner/scan-ticket/{code}/{eventId}` | Validate one ticket |

All authenticated requests send `Authorization: Bearer <token>` and `Accept: application/json`, matching the existing PWA.

## Token handling

- The Passport token is stored with `expo-secure-store`.
- Signing out removes the token from the device; it does not revoke the server-side Passport token. Revoke tokens from the existing Laravel admin if needed.

## Verification

```bash
npm run lint
npx expo install --check
npm run export
```

`npm run export` bundles the app with Metro without needing native Android/iOS projects or a paid account.

## Project layout

```text
mobile/
  App.js                    app state, auth, tab shell
  app.json                  Expo Go config and camera permission message
  src/api.js                fetch wrapper, API host detection and scanner endpoints
  src/storage.js            SecureStore token helper
  src/theme.js              light/dark design tokens from the web palette
  src/screens/              Login, Events, Scan and Settings screens
  src/components/ui.js      shared buttons, cards, fields and alerts
```

## Notes

- Camera access is requested when the Scan tab opens. If permission is denied, the manual code field still works.
- Use Expo Go in LAN mode, not tunnel mode, so the dev server host points at your Laravel machine.
- HTTP is convenient for local LAN testing. Use HTTPS when deploying the API beyond local development.
- QR payloads are sent exactly as scanned, trimmed, using `encodeURIComponent`, matching `resources/js/scanner-pwa.js`.
