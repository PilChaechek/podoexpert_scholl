import fs from 'fs-extra';
import path from 'path';

// Пути
const distDir = './dist/_astro'; // тут всё как у тебя, css лежит здесь
const targetDir = path.resolve('../prod/local/templates/medical_landing/'); // абсолютный путь в корневой prod
const targetFile = path.join(targetDir, 'styles.css');

// ищем первый css-файл в /dist/_astro
const cssFile = fs.readdirSync(distDir).find(f => f.endsWith('.css'));

if (!cssFile) {
	console.error('❌ CSS-файл не найден в dist/_astro');
	process.exit(1);
}

// копируем
fs.ensureDirSync(targetDir);
fs.copyFileSync(path.join(distDir, cssFile), targetFile);

console.log(`✅ CSS скопирован в ${targetFile}`);
