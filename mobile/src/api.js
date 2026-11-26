import Constants from 'expo-constants';

const FALLBACK_API_BASE_URL = 'http://10.0.2.2:8000';

export class ApiError extends Error {
  constructor(message, options = {}) {
    super(message);
    this.name = 'ApiError';
    this.status = options.status ?? 0;
    this.payload = options.payload ?? null;
    this.code = options.code ?? null;
  }
}

export function normalizeBaseUrl(value) {
  const trimmed = String(value ?? '').trim();

  if (!trimmed) {
    throw new Error('Enter the API server address.');
  }

  const withScheme = /^https?:\/\//i.test(trimmed) ? trimmed : `http://${trimmed}`;

  try {
    const parsed = new URL(withScheme);

    if (!['http:', 'https:'].includes(parsed.protocol)) {
      throw new Error('Use an http:// or https:// address.');
    }

    return parsed.origin;
  } catch (error) {
    if (error instanceof Error && error.message.includes('http://')) {
      throw error;
    }

    throw new Error('That API server address is not valid.');
  }
}

function devServerHost() {
  const hostUri = Constants.expoConfig?.hostUri || Constants.expoGoConfig?.debuggerHost;

  if (!hostUri) {
    return null;
  }

  const value = String(hostUri).trim();
  const withScheme = /^https?:\/\//i.test(value) ? value : `http://${value}`;

  try {
    return new URL(withScheme).hostname || null;
  } catch {
    return value.split(':')[0] || null;
  }
}

export function resolveApiBaseUrl() {
  const override = process.env.EXPO_PUBLIC_API_BASE_URL;

  if (override) {
    return normalizeBaseUrl(override);
  }

  const host = devServerHost();

  if (host) {
    return `http://${host}:8000`;
  }

  return FALLBACK_API_BASE_URL;
}

function errorMessageFrom(payload, fallback) {
  if (!payload || typeof payload !== 'object') {
    return fallback;
  }

  if (typeof payload.msg === 'string' && payload.msg.trim()) {
    return payload.msg;
  }

  if (typeof payload.message === 'string' && payload.message.trim()) {
    return payload.message;
  }

  const errors = payload.errors;

  if (errors && typeof errors === 'object') {
    const firstKey = Object.keys(errors)[0];
    const firstError = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey];

    if (typeof firstError === 'string' && firstError.trim()) {
      return firstError;
    }
  }

  return fallback;
}

async function apiFetch(path, options = {}) {
  const {
    method = 'GET',
    body,
    token,
    baseUrl = resolveApiBaseUrl(),
    timeoutMs = 15000,
  } = options;

  const serverUrl = normalizeBaseUrl(baseUrl);
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), timeoutMs);

  let response;

  try {
    response = await fetch(`${serverUrl}${path}`, {
      method,
      headers: {
        Accept: 'application/json',
        ...(body ? { 'Content-Type': 'application/json' } : {}),
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: body ? JSON.stringify(body) : undefined,
      signal: controller.signal,
    });
  } catch (error) {
    if (error?.name === 'AbortError') {
      throw new ApiError(
        'The server did not respond in time. Check the API address and try again.',
        { code: 'TIMEOUT' },
      );
    }

    throw new ApiError(
      `Cannot reach Laravel at ${serverUrl}. Check that your phone and computer share Wi-Fi and that Laravel is running on 0.0.0.0:8000.`,
      { code: 'NETWORK' },
    );
  } finally {
    clearTimeout(timeout);
  }

  let payload = null;
  const text = await response.text();

  if (text) {
    try {
      payload = JSON.parse(text);
    } catch {
      payload = { raw: text };
    }
  }

  if (response.status === 401) {
    throw new ApiError('Your session expired. Please sign in again.', {
      status: 401,
      payload,
      code: 'UNAUTHENTICATED',
    });
  }

  if (response.status === 403) {
    throw new ApiError('You are not assigned to that event.', {
      status: 403,
      payload,
      code: 'FORBIDDEN',
    });
  }

  if (response.status === 429) {
    throw new ApiError('Too many attempts. Please wait a moment and try again.', {
      status: 429,
      payload,
      code: 'RATE_LIMITED',
    });
  }

  if (!response.ok) {
    throw new ApiError(errorMessageFrom(payload, `Request failed (${response.status}).`), {
      status: response.status,
      payload,
    });
  }

  return payload ?? {};
}

export function login({ email, password, baseUrl }) {
  return apiFetch('/api/scanner/login', {
    method: 'POST',
    body: { email: String(email ?? '').trim(), password },
    baseUrl,
  });
}

export function fetchEvents({ token, baseUrl }) {
  return apiFetch('/api/scanner/events', { token, baseUrl });
}

export function scanTicket({ code, eventId, token, baseUrl }) {
  const ticketCode = encodeURIComponent(String(code ?? '').trim());

  return apiFetch(`/api/scanner/scan-ticket/${ticketCode}/${eventId}`, { token, baseUrl });
}
