import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  scenarios: {
    queue_reads: {
      executor: 'constant-arrival-rate',
      rate: 20,
      timeUnit: '1s',
      duration: '60s',
      preAllocatedVUs: 20,
      maxVUs: 50,
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<500'],
  },
};

const baseUrl = __ENV.BASE_URL || 'https://localhost:8443';
const token = __ENV.ACCESS_TOKEN;

export default function () {
  const response = http.get(`${baseUrl}/api/queues`, {
    headers: { Authorization: `Bearer ${token}` },
    insecureSkipTLSVerify: true,
  });

  check(response, {
    'queues returned 200': (r) => r.status === 200,
  });
  sleep(0.01);
}
