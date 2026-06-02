<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use FontLib\Font;
use Dompdf\Dompdf;
use Dompdf\Options;

class LoadTimesNewRomanFont extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'font:load-times-new-roman';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load Times New Roman fonts into dompdf';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fontDir = storage_path('fonts');
        
        if (!is_dir($fontDir)) {
            $this->error("Font directory does not exist: {$fontDir}");
            return 1;
        }

        $fonts = [
            'TimesNewRoman.ttf' => ['normal' => 'normal'],
            'TimesNewRoman-Bold.ttf' => ['normal' => 'bold'],
            'TimesNewRoman-Italic.ttf' => ['normal' => 'italic'],
            'TimesNewRoman-BoldItalic.ttf' => ['normal' => 'bold_italic'],
        ];

        $this->info('Loading Times New Roman fonts...');

        foreach ($fonts as $fontFile => $variants) {
            $fontPath = $fontDir . '/' . $fontFile;
            
            if (!file_exists($fontPath)) {
                $this->warn("Font file not found: {$fontFile}");
                continue;
            }

            $this->info("Processing: {$fontFile}");

            try {
                // Load font using FontLib
                $font = Font::load($fontPath);
                $font->parse();

                // Generate UFM file
                $fontName = pathinfo($fontFile, PATHINFO_FILENAME);
                $ufmPath = $fontDir . '/' . $fontName . '.ufm';
                
                // Save UFM file
                $font->saveAdobeFontMetrics($ufmPath);
                
                $this->info("  ✓ Generated UFM: {$fontName}.ufm");
            } catch (\Exception $e) {
                $this->error("  ✗ Error processing {$fontFile}: " . $e->getMessage());
                continue;
            }
        }

        // Update installed-fonts.json
        $this->updateInstalledFonts($fontDir, $fonts);

        $this->info("\n✓ Times New Roman fonts loaded successfully!");
        $this->info("You can now use 'Times New Roman' as font-family in your PDF templates.");
        
        return 0;
    }

    /**
     * Update installed-fonts.json file
     */
    private function updateInstalledFonts($fontDir, $fonts)
    {
        $installedFontsFile = $fontDir . '/installed-fonts.json';
        $installedFonts = [];

        if (file_exists($installedFontsFile)) {
            $installedFonts = json_decode(file_get_contents($installedFontsFile), true) ?: [];
        }

        // Register Times New Roman font family
        $installedFonts['times new roman'] = [
            'normal' => 'TimesNewRoman',
            'bold' => 'TimesNewRoman-Bold',
            'italic' => 'TimesNewRoman-Italic',
            'bold_italic' => 'TimesNewRoman-BoldItalic',
        ];

        file_put_contents($installedFontsFile, json_encode($installedFonts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->info("  ✓ Updated installed-fonts.json");
    }
}
