import http from 'k6/http';
import {sleep} from 'k6';

export const options = {
    vus: __ENV.VUS ? parseInt(__ENV.VUS) : 50,
    duration: __ENV.DURATION || '30s',
    thresholds: {
        http_req_failed: ['rate<0.005'],
        http_req_duration: ['p(95)<250'],
    },
};

const url = __ENV.URL || 'http://localhost:8080/health';

export default function () {
    http.get(url);
    sleep(0.01);
}
