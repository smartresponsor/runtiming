

import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8080';
const P95_MS = parseInt(__ENV.P95_MS || '1200', 10);

const thresholds = JSON.parse(open('./thresholds-runtime.json'));
thresholds.http_req_duration = thresholds.http_req_duration || [];
thresholds.http_req_duration = thresholds.http_req_duration.filter((x) => !String(x).includes('p(95)<'))
  .concat([`p(95)<${P95_MS}`]);

export const options = {
  stages: [
    { duration: __ENV.RAMP_UP || '30s', target: parseInt(__ENV.VUS_SPIKE || '50', 10) },
    { duration: __ENV.HOLD || '30s', target: parseInt(__ENV.VUS_SPIKE || '50', 10) },
    { duration: __ENV.RAMP_DOWN || '30s', target: 0 },
  ],
  thresholds,
};

function url(path) {
  const base = BASE_URL.replace(/\/$/, '');
  return base + (path.startsWith('/') ? path : '/' + path);
}

export default function () {
  const r = http.get(url('/status/worker'), { tags: { name: 'status_worker' } });
  check(r, { 'status/worker 200': (res) => res.status === 200 });
  sleep(0.05);
}
