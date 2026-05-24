# Guía de Integración - Frontend React con Backend Laravel

## 1. Configuración del Cliente HTTP

Crea `src/utils/apiClient.js` en tu frontend React:

```javascript
import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  withCredentials: true, // Importante para CORS
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor para agregar el token en cada request
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('fitzone_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Interceptor para manejar errores
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expirado o inválido
      localStorage.removeItem('fitzone_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default apiClient;
```

## 2. Login con Supabase

En tu hook o servicio de autenticación:

```javascript
import apiClient from '@/utils/apiClient';
import { supabase } from '@/lib/supabase';

export const loginWithSupabase = async (email, password) => {
  // 1. Login en Supabase
  const { data, error } = await supabase.auth.signInWithPassword({
    email,
    password,
  });

  if (error) throw error;

  // 2. Obtener el access_token de Supabase
  const supabaseToken = data.session.access_token;

  // 3. Enviar el token al backend para obtener el token de Sanctum
  const response = await apiClient.post('/login', {
    email,
    password, // O puedes usar solo el email
  });

  if (response.data.success) {
    // 4. Guardar el token de Sanctum en localStorage
    localStorage.setItem('fitzone_token', response.data.token);
    return response.data;
  }
};
```

## 3. Rutas Públicas (SIN token)

```javascript
// GET /api/tienda/productos
const getProductos = async () => {
  const response = await apiClient.get('/tienda/productos');
  return response.data;
};

// GET /api/tienda/categorias
const getCategorias = async () => {
  const response = await apiClient.get('/tienda/categorias');
  return response.data;
};

// GET /api/tienda/marcas
const getMarcas = async () => {
  const response = await apiClient.get('/tienda/marcas');
  return response.data;
};
```

## 4. Rutas Protegidas (CON token)

El interceptor agregará automáticamente el header `Authorization: Bearer {token}`:

```javascript
// GET /api/me (obtener usuario actual)
const getCurrentUser = async () => {
  const response = await apiClient.get('/me');
  return response.data;
};

// POST /api/logout
const logout = async () => {
  await apiClient.post('/logout');
  localStorage.removeItem('fitzone_token');
};

// GET /api/dashboard
const getDashboard = async () => {
  const response = await apiClient.get('/dashboard');
  return response.data;
};
```

## 5. Variables de Entorno (.env)

```
VITE_API_URL=http://localhost:8000/api
```

## 6. Instalación de Dependencias

```bash
npm install axios
# o
yarn add axios
```

## Testing en Postman/Insomnia

### Rutas Públicas:
```
GET http://localhost:8000/api/tienda/productos
GET http://localhost:8000/api/tienda/categorias
GET http://localhost:8000/api/tienda/marcas
```

### Rutas Protegidas (Agregar header):
```
Header: Authorization: Bearer {token_aqui}

GET http://localhost:8000/api/me
POST http://localhost:8000/api/logout
```

## Solución de Problemas

### Error: CORS policy
✅ Verifica que `localhost:5173` esté en `allowed_origins` del config/cors.php
✅ Usa `withCredentials: true` en axios

### Error: 401 Unauthorized
✅ El token no fue enviado o es inválido
✅ Verifica que localStorage tenga `fitzone_token`
✅ Verifica el header `Authorization: Bearer {token}`

### Error: 422 Validation Error
✅ Verifica que los datos enviados sean válidos
✅ Lee el campo `errors` en la respuesta

