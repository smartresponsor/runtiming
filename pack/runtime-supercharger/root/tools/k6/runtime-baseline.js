

import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8080';
const DURATION = __ENV.DURATION || '30s';
const VUS = parseInt(__ENV.VUS || '5', 10);
const THRESHOLDS = JSON.parse(open('./thresholds-runtime.json'));

export const options = {
  vus: VUS,
  duration: DURATION,
  thresholds: THRESHOLDS,
};

function url(path) {
  const base = BASE_URL.replace(/\/$/, '');
  return base + (path.startsWith('/') ? path : '/' + path);
}

export default function () {
  const r1 = http.get(url('/status/worker'), { tags: { name: 'status_worker' } });
  check(r1, {
    'status/worker 200': (res) => res.status === 200,
    'status/worker json': (res) => (res.headers['Content-Type'] || '').includes('application/json') || res.body.startsWith('{'),
  });

  const r2 = http.get(url('/status/host'), { tags: { name: 'status_host' } });
  check(r2, {
    'status/host 200': (res) => res.status === 200,
  });

  // Metrics is intentionally probed at a low ratio to avoid excessive payload.
  if (Math.random() < 0.1) {
    const r3 = http.get(url('/metrics'), { tags: { name: 'metrics' } });
    check(r3, {
      'metrics 200': (res) => res.status === 200,
      'metrics has reset_total': (res) => res.body.includes('runtime_supercharger_reset_total'),
    });
  }

  sleep(0.2);
}
