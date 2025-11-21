#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Dynamic import for ES modules
let axios;
(async () => {
    try {
        const axiosModule = await import('axios');
        axios = axiosModule.default;
    } catch (error) {
        console.error('❌ axios is required. Install it with: npm install axios');
        process.exit(1);
    }

    await main();
})();

async function main() {
    console.log('🔗 Starting link checker...');

    // Load configuration
    const config = loadConfig();
    const baseUrl = process.env.APP_URL || config.baseUrl || 'http://localhost:8000';

    console.log(`🌍 Base URL: ${baseUrl}`);
    console.log(`📄 Checking ${config.routes.length} routes...`);

    const results = [];
    let failedCount = 0;

    for (const route of config.routes) {
        const url = `${baseUrl}${route}`;
        console.log(`📍 Checking: ${url}`);

        try {
            const startTime = Date.now();
            const response = await axios.get(url, {
                timeout: config.timeout || 10000,
                maxRedirects: 5,
                validateStatus: function (status) {
                    // Accept 2xx and 3xx status codes
                    return status >= 200 && status < 400;
                }
            });
            const responseTime = Date.now() - startTime;

            const result = {
                url,
                status: response.status,
                responseTime,
                success: true,
                error: null,
                timestamp: new Date().toISOString()
            };

            results.push(result);

            const statusColor = response.status < 300 ? '✅' : '🔄';
            const timeColor = responseTime > 2000 ? '🐌' : responseTime > 1000 ? '⚠️' : '⚡';
            console.log(`   ${statusColor} ${response.status} ${timeColor} ${responseTime}ms`);

        } catch (error) {
            failedCount++;
            const result = {
                url,
                status: error.response?.status || 0,
                responseTime: null,
                success: false,
                error: error.message,
                timestamp: new Date().toISOString()
            };

            results.push(result);
            console.log(`   ❌ ${error.response?.status || 'ERROR'} - ${error.message}`);
        }
    }

    // Generate report
    const report = {
        summary: {
            totalRoutes: config.routes.length,
            successfulRoutes: results.filter(r => r.success).length,
            failedRoutes: failedCount,
            averageResponseTime: calculateAverageResponseTime(results),
            slowRoutes: results.filter(r => r.responseTime > 2000).length,
            timestamp: new Date().toISOString()
        },
        routes: results,
        config: {
            baseUrl,
            timeout: config.timeout || 10000,
            checkDate: new Date().toISOString()
        }
    };

    // Save report
    const reportPath = path.join(process.cwd(), 'storage', 'logs', 'link-check-report.json');
    ensureDirectoryExists(path.dirname(reportPath));
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));

    // Display summary
    console.log('\n📊 SUMMARY');
    console.log(`✅ Successful: ${report.summary.successfulRoutes}/${report.summary.totalRoutes}`);
    console.log(`❌ Failed: ${report.summary.failedRoutes}`);
    console.log(`⚡ Average response time: ${report.summary.averageResponseTime}ms`);
    console.log(`🐌 Slow routes (>2s): ${report.summary.slowRoutes}`);
    console.log(`📄 Report saved to: ${reportPath}`);

    if (failedCount > 0) {
        console.log('\n❌ FAILED ROUTES:');
        results.filter(r => !r.success).forEach(r => {
            console.log(`   ${r.url} - ${r.error}`);
        });
    }

    // Exit with appropriate code
    process.exit(failedCount > 0 ? 1 : 0);
}

function loadConfig() {
    const configPath = path.join(process.cwd(), 'link-check-config.json');

    if (fs.existsSync(configPath)) {
        try {
            const configData = fs.readFileSync(configPath, 'utf8');
            return JSON.parse(configData);
        } catch (error) {
            console.warn(`⚠️ Error reading config file: ${error.message}`);
        }
    }

    // Default configuration
    return {
        baseUrl: 'http://localhost:8000',
        timeout: 10000,
        routes: [
            '/',
            '/collections',
            '/about',
            '/contact',
            '/cart',
            '/login',
            '/register'
        ]
    };
}

function calculateAverageResponseTime(results) {
    const successfulResults = results.filter(r => r.success && r.responseTime !== null);
    if (successfulResults.length === 0) return 0;

    const total = successfulResults.reduce((sum, r) => sum + r.responseTime, 0);
    return Math.round(total / successfulResults.length);
}

function ensureDirectoryExists(dirPath) {
    if (!fs.existsSync(dirPath)) {
        fs.mkdirSync(dirPath, { recursive: true });
    }
}
