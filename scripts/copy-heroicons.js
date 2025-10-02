import { promises as fs } from 'node:fs';
import path from 'node:path';

const variants = [
    { name: 'outline', sources: ['optimized/24/outline', '24/outline'] },
    { name: 'solid', sources: ['optimized/24/solid', '24/solid'] },
    { name: 'mini', sources: ['optimized/20/solid', '20/solid'] },
];

const iconsByVariant = {
    outline: [
        'users',
        'chart-bar',
        'identification',
        'ellipsis-horizontal',
        'pencil',
        'trash',
        'eye',
        'funnel',
        'user-circle',
        'arrow-left-end-on-rectangle',
    ],
    solid: [],
    mini: [],
};

const sourceRoot = path.resolve('node_modules', 'heroicons');
const destinationRoot = path.resolve('resources', 'svg', 'heroicons');

const resolveSourceDirectory = async (sources) => {
    for (const relative of sources) {
        const candidate = path.join(sourceRoot, relative);
        try {
            await fs.access(candidate);
            return candidate;
        } catch (error) {
            // Keep iterating while we search for a valid candidate
        }
    }

    throw new Error(
        'Heroicons source directory not found. The package structure may have changed. ' +
            'Verify that the `heroicons` dependency is installed and update scripts/copy-heroicons.js if necessary.'
    );
};

const copyVariant = async ({ name, sources }) => {
    const sourceDir = await resolveSourceDirectory(sources);
    const destinationDir = path.join(destinationRoot, name);

    await fs.mkdir(destinationDir, { recursive: true });

    const requestedIcons = iconsByVariant[name] ?? [];

    let files;

    if (requestedIcons.length > 0) {
        files = await Promise.all(
            requestedIcons.map(async (icon) => {
                const filename = `${icon}.svg`;
                const sourcePath = path.join(sourceDir, filename);

                try {
                    await fs.access(sourcePath);
                } catch (error) {
                    throw new Error(`Icon ${filename} not found in ${sourceDir}.`);
                }

                return { name: filename, sourcePath };
            })
        );
    } else {
        files = (await fs.readdir(sourceDir, { withFileTypes: true }))
            .filter((entry) => entry.isFile() && entry.name.endsWith('.svg'))
            .map((entry) => ({ name: entry.name, sourcePath: path.join(sourceDir, entry.name) }));
    }

    await Promise.all(
        files.map(async (file) => {
            const destinationPath = path.join(destinationDir, file.name);
            await fs.copyFile(file.sourcePath, destinationPath);
        })
    );

    return { name, count: files.length };
};

const main = async () => {
    try {
        await fs.mkdir(destinationRoot, { recursive: true });
        const results = await Promise.all(variants.map(copyVariant));

        results.forEach(({ name, count }) => {
            console.log(`Copied ${count} ${name} icons.`);
        });

        console.log('Heroicons copied successfully.');
    } catch (error) {
        console.error(error.message);
        process.exitCode = 1;
    }
};

main();
