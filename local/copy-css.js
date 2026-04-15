import fs from 'fs-extra';
import path from 'path';

const distDir = './dist/_astro';
const targetDir = path.resolve('../prod/local/templates/medical_landing/');
const targetScriptsDir = path.join(targetDir, 'scripts');

const astroFiles = fs.readdirSync(distDir);

// CSS
const cssFile = astroFiles.find(f => f.endsWith('.css'));
if (!cssFile) {
	console.error('❌ CSS-файл не найден в dist/_astro');
	process.exit(1);
}
fs.ensureDirSync(targetDir);
fs.copyFileSync(path.join(distDir, cssFile), path.join(targetDir, 'styles.css'));
console.log(`✅ CSS скопирован в ${path.join(targetDir, 'styles.css')}`);

// JS (из public/scripts/ → dist/scripts/ → prod)
const distScriptsDir = './dist/scripts';
const mainJsSrc = path.join(distScriptsDir, 'main.js');
if (!fs.existsSync(mainJsSrc)) {
	console.warn('⚠️  dist/scripts/main.js не найден, пропускаем');
} else {
	fs.ensureDirSync(targetScriptsDir);
	fs.copyFileSync(mainJsSrc, path.join(targetScriptsDir, 'main.js'));
	console.log(`✅ JS скопирован в ${path.join(targetScriptsDir, 'main.js')}`);
}
