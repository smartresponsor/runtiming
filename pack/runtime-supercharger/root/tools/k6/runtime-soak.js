

import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8080';
const DURATION = __ENV.DURATION || '15m';
const VUS = parseInt(__ENV.VUS || '10', 10);
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
  // 90% status checks, 10% metrics.
  if (Math.random() < 0.9) {
    const r = http.get(url('/status/worker'), { tags: { name: 'status_worker' } });
    check(r, {
      'status/worker 200': (res) => res.status === 200,
    });
  } else {
    const r = http.get(url('/metrics'), { tags: { name: 'metrics' } });
    check(r, {
      'metrics 200': (res) => res.status === 200,
    });
  }

  sleep(0.3);
}
