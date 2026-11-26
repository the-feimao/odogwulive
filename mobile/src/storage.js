import * as SecureStore from 'expo-secure-store';

const TOKEN_KEY = 'odogwu.scanner.token';
const API_BASE_URL_KEY = 'odogwu.scanner.apiBaseUrl';

export const storage = {
  getToken: () => SecureStore.getItemAsync(TOKEN_KEY),
  setToken: (token) => SecureStore.setItemAsync(TOKEN_KEY, token),
  removeToken: () => SecureStore.deleteItemAsync(TOKEN_KEY),
  getApiBaseUrl: () => SecureStore.getItemAsync(API_BASE_URL_KEY),
  setApiBaseUrl: (baseUrl) => SecureStore.setItemAsync(API_BASE_URL_KEY, baseUrl),
};
