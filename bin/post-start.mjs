/**
 * Post-start script for wp-env.
 *
 * Increases Apache's LimitRequestFieldSize so the wp-admin doesn't reject
 * requests when the browser carries large cookies from other localhost sites.
 */

import { execSync } from 'node:child_process';

const containers = execSync('docker ps --format "{{.ID}} {{.Names}}"', { encoding: 'utf8' })
	.trim()
	.split('\n');

for (const line of containers) {
	const [id, name] = line.split(' ');
	// Match both the dev and test WordPress containers, but not cli/mysql.
	if (!name.includes('wordpress') || name.includes('cli') || name.includes('mysql')) {
		continue;
	}

	try {
		execSync(
			`docker exec -u root ${id} bash -c "echo 'LimitRequestFieldSize 32768' > /etc/apache2/conf-enabled/request-limits.conf && apache2ctl graceful"`,
			{ stdio: 'pipe' }
		);
		console.log(`Applied LimitRequestFieldSize fix to ${name}`);
	} catch {
		console.warn(`Warning: could not apply header limit fix to ${name}`);
	}
}
