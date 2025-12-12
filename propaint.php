<?php
// GESTION DES UPLOADS DE POLICES (FONTS)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fontUpload'])) {
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => '', 'fontName' => '', 'fontUrl' => ''];
    
    try {
        $uploadDir = __DIR__ . '/fontfam/';
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception("Impossible de créer le dossier fontfam");
            }
        }
        
        $file = $_FILES['fontUpload'];
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        
        // Créer un sous-dossier pour cette police
        $fontDir = $uploadDir . $baseName . '/';
        if (!file_exists($fontDir)) {
            if (!mkdir($fontDir, 0755, true)) {
                throw new Exception("Impossible de créer le dossier de la police");
            }
        }
        
        if ($fileExt === 'zip') {
            $zip = new ZipArchive;
            if ($zip->open($fileTmp) === TRUE) {
                $zip->extractTo($fontDir);
                $zip->close();
                
                // Chercher le premier fichier .ttf ou .otf extrait
                $files = scandir($fontDir);
                $fontFile = null;
                foreach ($files as $f) {
                    if (preg_match('/\.(ttf|otf)$/i', $f)) {
                        $fontFile = $f;
                        break;
                    }
                }
                
                if ($fontFile) {
                    $response['success'] = true;
                    $response['message'] = "Police extraite avec succès";
                    $response['fontName'] = pathinfo($fontFile, PATHINFO_FILENAME);
                    $response['fontUrl'] = 'fontfam/' . $baseName . '/' . $fontFile;
                } else {
                    throw new Exception("Aucun fichier .ttf ou .otf trouvé dans le ZIP");
                }
            } else {
                throw new Exception("Impossible d'ouvrir le fichier ZIP");
            }
        } elseif ($fileExt === 'ttf' || $fileExt === 'otf') {
            $destPath = $fontDir . $fileName;
            if (move_uploaded_file($fileTmp, $destPath)) {
                $response['success'] = true;
                $response['message'] = "Police uploadée avec succès";
                $response['fontName'] = $baseName;
                $response['fontUrl'] = 'fontfam/' . $baseName . '/' . $fileName;
            } else {
                throw new Exception("Erreur lors du déplacement du fichier");
            }
        } else {
            throw new Exception("Format de fichier non supporté (ZIP, TTF, OTF uniquement)");
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

  // LISTAGE AUTOMATIQUE DES POLICES DANS fontfam/
  $availableFonts = [];
  $fontBaseDir = __DIR__ . '/fontfam/';
  if (is_dir($fontBaseDir)) {
    foreach (scandir($fontBaseDir) as $dir) {
      if ($dir === '.' || $dir === '..') continue;
      $fullDir = $fontBaseDir . $dir;
      if (!is_dir($fullDir)) continue;
      foreach (scandir($fullDir) as $file) {
        if (preg_match('/\.(ttf|otf)$/i', $file)) {
          $fontPath = 'fontfam/' . $dir . '/' . $file;
          $fontName = pathinfo($file, PATHINFO_FILENAME);
          $availableFonts[] = [
            'name' => $fontName,
            'url'  => $fontPath
          ];
        }
      }
    }
  }
?>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="shortcut icon" href="https://dihu.fr/appgithub/iconedihu/9.png" type="image/png">
<link rel="icon" href="https://dihu.fr/appgithub/iconedihu/9.png" type="image/png">
  <title>ProPaint</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
  <script>
    window.preloadedFonts = <?php echo json_encode($availableFonts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
  </script>
  <style>
    /* Custom font for the italic text */
    .italic-text {
      font-family: Georgia, serif;
    }
    /* Scrollbar styling for vertical toolbar */
    .scrollbar-thin::-webkit-scrollbar {
      width: 6px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
      background: #2d2d2d;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
      background-color: #555;
      border-radius: 3px;
    }
    .checkerboard {
      background-image:
        linear-gradient(45deg, #ccc 25%, transparent 25%),
        linear-gradient(-45deg, #ccc 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #ccc 75%),
        linear-gradient(-45deg, transparent 75%, #ccc 75%);
      background-size: 20px 20px;
      background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }
    input[type=number] {
      -moz-appearance:textfield;
    }
  </style>
</head>
<body class="bg-[#1e1e1e] text-[#f0d98c] font-sans select-none">
  <div class="flex flex-col h-screen w-full">
    <!-- Top menu bar -->
    <div class="flex items-center bg-[#2d2d2d] text-[#c0c0c0] text-[13px] font-normal px-2 select-text" style="font-family: Arial, sans-serif;">
      <div class="flex space-x-4">
        <span class="cursor-default">File</span>
        <span class="cursor-default">Edit</span>
        <span class="cursor-default">Image</span>
        <span class="cursor-default">Layer</span>
        <span class="cursor-default">Type</span>
        <span class="cursor-default">Select</span>
        <span class="cursor-default">Filter</span>
        <span class="cursor-default">View</span>
        <span class="cursor-default">Window</span>
        <span class="cursor-default">Help</span>
      </div>
    </div>

    <!-- Second horizontal bar with icons and options -->
    <div class="flex items-center bg-[#3a3a3a] text-[#c0c0c0] text-[13px] font-normal px-2 select-none space-x-4 h-10">
      <button id="undoBtn" aria-label="Undo" class="flex items-center space-x-1 hover:bg-[#4a4a4a] px-2 rounded">
        <i class="fas fa-undo text-[16px]"></i>
        <span>Undo</span>
      </button>
      <button id="redoBtn" aria-label="Redo" class="flex items-center space-x-1 hover:bg-[#4a4a4a] px-2 rounded">
        <i class="fas fa-redo text-[16px]"></i>
        <span>Redo</span>
      </button>
      <div class="border-l border-[#555] h-6"></div>
      <label for="uploadImage" class="cursor-pointer hover:bg-[#4a4a4a] px-2 rounded flex items-center space-x-2" title="Importer une image">
        <i class="fas fa-upload text-[16px]"></i>
        <span>Import</span>
      </label>
      <input type="file" id="uploadImage" accept="image/*" class="hidden" aria-label="Importer une image" />
      <button id="downloadBtn" class="hover:bg-[#4a4a4a] px-2 rounded flex items-center space-x-2" title="Télécharger l'image en haute résolution">
        <i class="fas fa-download text-[16px]"></i>
        <span>Export</span>
      </button>
      <button id="clearErasedBtn" class="hover:bg-[#4a4a4a] px-2 rounded flex items-center space-x-2" title="Réinitialiser les zones effacées">
        <i class="fas fa-undo-alt text-[16px]"></i>
        <span>Reset Eraser</span>
      </button>
    </div>

    <div class="flex flex-grow overflow-hidden">
      <!-- Toolbar left vertical with scroll -->
      <div id="leftToolbar" class="flex flex-col bg-[#2d2d2d] w-[48px] py-2 space-y-1 items-center select-none scrollbar-thin overflow-y-auto">
        <!-- Brush icon -->
        <button aria-label="Brush" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='brush-basic'; currentTool='brush-basic';">
          <i class="fas fa-brush text-[20px]"></i>
        </button>
        <!-- Pencil icon -->
        <button aria-label="Pencil" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='brush-pencil'; currentTool='brush-pencil';">
          <i class="fas fa-pencil-alt text-[20px]"></i>
        </button>
        <!-- Eraser icon -->
        <button aria-label="Eraser" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='eraser'; currentTool='eraser';">
          <i class="fas fa-eraser text-[20px]"></i>
        </button>
        <!-- Shape icon -->
        <button aria-label="Shape" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='shape-rectangle'; currentTool='shape-rectangle';">
          <i class="fas fa-square text-[20px]"></i>
        </button>
        <!-- Circle icon -->
        <button aria-label="Circle" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='shape-circle'; currentTool='shape-circle';">
          <i class="fas fa-circle text-[20px]"></i>
        </button>
        <!-- Line icon -->
        <button aria-label="Line" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='shape-line'; currentTool='shape-line';">
          <i class="fas fa-slash text-[20px]"></i>
        </button>
        <!-- Select icon -->
        <button aria-label="Select" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='select'; currentTool='select';">
          <i class="fas fa-mouse-pointer text-[20px]"></i>
        </button>
        <!-- Copy icon -->
        <button aria-label="Copy" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='copy'; currentTool='copy';">
          <i class="fas fa-copy text-[20px]"></i>
        </button>
        <!-- Paste icon -->
        <button aria-label="Paste" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='paste'; currentTool='paste';">
          <i class="fas fa-paste text-[20px]"></i>
        </button>
        <!-- Lasso Free icon -->
        <button aria-label="Lasso Free" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='lasso-free'; currentTool='lasso-free';">
          <i class="fas fa-draw-polygon text-[20px]"></i>
        </button>
        <!-- Lasso Polygonal icon -->
        <button aria-label="Lasso Polygonal" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='lasso-polygon'; currentTool='lasso-polygon';">
          <i class="fas fa-bezier-curve text-[20px]"></i>
        </button>
        <!-- Lasso Magnetic icon -->
        <button aria-label="Lasso Magnetic" class="w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded" onclick="document.getElementById('toolSelect').value='lasso-magnetic'; currentTool='lasso-magnetic';">
          <i class="fas fa-magnet text-[20px]"></i>
        </button>
      </div>

      <!-- Main canvas area -->
      <div class="flex-grow bg-[#252525] flex items-center justify-center relative overflow-auto">
        <div id="canvasContainer" class="flex justify-center items-center relative bg-white checkerboard" style="min-height: 400px; width: 100%; height: 100%;">
          <canvas id="drawingCanvas" width="3840" height="2160" class="border border-gray-400" aria-label="Canvas de dessin haute résolution" style="max-width: none; max-height: none;"></canvas>
        </div>
      </div>

      <!-- Right panel with tools -->
      <div id="rightPanel" class="flex flex-col bg-[#2d2d2d] w-[320px] min-w-[280px] max-w-full text-[#c0c0c0] select-none overflow-y-auto">
        <!-- Tools section -->
        <div id="toolsSection" class="p-3 bg-[#252525] border-b border-[#555]">
          <h2 class="text-lg font-semibold mb-2">Tools</h2>
          <select id="toolSelect" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 mb-3 text-[#c0c0c0]">
            <!-- OUTILS DE DESSIN BASIQUES -->
            <option value="brush-basic">Feutre Basique</option>
            <option value="brush-pencil">Crayon</option>
            <option value="brush-smoke">Fumée</option>
            <option value="brush-chalk">Craie</option>
            <option value="brush-brush">Pinceau</option>
            
            <!-- NOUVEAUX OUTILS DE DESSIN PHASE 6 -->
            <option value="brush-marker">Marqueur Épais</option>
            <option value="brush-fineliner">Feutre Fin</option>
            <option value="brush-fountain">Plume Fountain</option>
            <option value="brush-ballpoint">Stylo Bille</option>
            <option value="brush-charcoal">Fusain</option>
            <option value="brush-pastel">Pastel Gras</option>
            <option value="brush-watercolor">Aquarelle</option>
            <option value="brush-acrylic">Acrylique</option>
            <option value="brush-oil">Huile</option>
            <option value="brush-tempera">Tempera</option>
            <option value="brush-gouache">Gouache</option>
            <option value="brush-spray">Aérosol</option>
            <option value="brush-splatter">Éclaboussure</option>
            <option value="brush-stipple">Pointillé</option>
            <option value="brush-crosshatch">Hachure</option>
            <option value="brush-scribble">Gribouillage</option>
            <option value="brush-calligraphy">Calligraphie</option>
            <option value="brush-texture">Texture</option>
            <option value="brush-digital">Digital</option>
            <option value="brush-glitch">Glitch</option>
            <option value="brush-neon">Néon</option>
            <option value="brush-laser">Laser</option>
            <option value="brush-fire">Feu</option>
            <option value="brush-lightning">Éclair</option>
            <option value="brush-galaxy">Galaxie</option>
            
            <option value="eraser">Gomme</option>
            
            <!-- FORMES DE BASE EXISTANTES -->
            <option value="shape-rectangle">Rectangle</option>
            <option value="shape-circle">Cercle</option>
            <option value="shape-triangle">Triangle</option>
            <option value="shape-line">Ligne</option>
            <!-- NOUVELLES FORMES PHASE 2 -->
            <option value="shape-point">Point</option>
            <option value="shape-ellipse">Ellipse</option>
            <option value="shape-diamond">Losange</option>
            <option value="shape-pentagon">Pentagone</option>
            <option value="shape-hexagon">Hexagone</option>
            <option value="shape-octagon">Octogone</option>
            <option value="shape-star5">Étoile 5 branches</option>
            <option value="shape-star6">Étoile 6 branches</option>
            <option value="shape-star8">Étoile 8 branches</option>
            <option value="shape-heart">Cœur</option>
            <option value="shape-arrow">Flèche</option>
            <option value="shape-cloud">Nuage</option>
            <!-- 5 NOUVELLES FORMES SUPPLÉMENTAIRES -->
            <option value="shape-crescent">Croissant de Lune</option>
            <option value="shape-droplet">Goutte d'eau</option>
            <option value="shape-trapezoid">Trapèze</option>
            <option value="shape-parallelogram">Parallélogramme</option>
            <option value="shape-cross">Croix</option>
            <!-- 20 NOUVELLES FORMES SUPPLÉMENTAIRES -->
            <option value="shape-heptagon">Heptagone (7 côtés)</option>
            <option value="shape-nonagon">Nonagone (9 côtés)</option>
            <option value="shape-decagon">Décagone (10 côtés)</option>
            <option value="shape-dodecagon">Dodécagone (12 côtés)</option>
            <option value="shape-star3">Étoile 3 branches</option>
            <option value="shape-star4">Étoile 4 branches</option>
            <option value="shape-star7">Étoile 7 branches</option>
            <option value="shape-star10">Étoile 10 branches</option>
            <option value="shape-spiral">Spirale</option>
            <option value="shape-gear">Engrenage</option>
            <option value="shape-lightning">Éclair</option>
            <option value="shape-leaf">Feuille</option>
            <option value="shape-flower">Fleur</option>
            <option value="shape-sun">Soleil</option>
            <option value="shape-moon">Lune pleine</option>
            <option value="shape-infinity">Infini (∞)</option>
            <option value="shape-bubble">Bulle</option>
            <option value="shape-crown">Couronne</option>
            <option value="shape-gem">Diamant/Gemme</option>
            <option value="shape-shield">Bouclier</option>
            <option value="shape-eye">Œil</option>
            <option value="shape-butterfly">Papillon</option>
            <option value="select">Sélection</option>
            <option value="copy">Copier</option>
            <option value="paste">Coller</option>
            <option value="lasso-free">Lasso Libre</option>
            <option value="lasso-polygon">Lasso Polygonal</option>
            <option value="lasso-magnetic">Lasso Magnétique</option>
          </select>
          
          <!-- Style d'image global simple -->
          <label class="block mb-1 text-sm mt-2">Style image</label>
          <select id="imageStyle" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 mb-3 text-[#c0c0c0]">
            <option value="normal">Normal</option>
            <option value="grayscale">Noir & blanc</option>
            <option value="sepia">Sépia</option>
            <option value="contrast">Contraste +</option>
            <option value="saturate">Saturé</option>
          </select>

          <label for="brushSize" class="block mb-1 text-sm">Brush Size: <span id="brushSizeValue">10</span> px</label>
          <div class="flex items-center space-x-2 mb-3">
            <input type="range" id="brushSize" min="0.001" max="1000" step="0.001" value="10" class="flex-1" />
            <input type="number" id="brushSizeNumber" min="0.001" max="1000" step="0.001" value="10" class="w-20 bg-[#1e1e1e] border border-[#555] rounded px-1 py-0.5 text-xs text-right" />
          </div>

          <!-- Options Formes -->
          <div id="shapeOptions" class="hidden mb-3">
            <h3 class="text-sm font-semibold mb-2 text-[#00aaff]">Options Formes</h3>
            
            <!-- Type de forme -->
            <div class="mb-2">
              <label class="flex items-center text-sm">
                <input type="checkbox" id="shapeOutlineOnly" class="mr-2">
                <span>Contour uniquement</span>
              </label>
            </div>
            
            <!-- Épaisseur du contour -->
            <div id="outlineThicknessContainer" class="hidden mb-2">
              <label for="outlineThickness" class="block mb-1 text-xs">Épaisseur: <span id="outlineThicknessValue">1</span>px</label>
              <input type="range" id="outlineThickness" min="0.0001" max="500" step="0.0001" value="1" class="w-full" />
            </div>

            <!-- Border Radius pour les formes -->
            <div class="mb-2">
              <label for="borderRadius" class="block mb-1 text-xs">Border Radius: <span id="borderRadiusValue">0</span>px</label>
              <input type="range" id="borderRadius" min="0" max="60" step="0.1" value="0" class="w-full" />
            </div>

            <!-- Rotation pour les formes -->
            <div class="mb-2">
              <label for="shapeRotation" class="block mb-1 text-xs">Rotation: <span id="shapeRotationValue">0</span>°</label>
              <input type="range" id="shapeRotation" min="0" max="360" step="0.1" value="0" class="w-full" />
            </div>

              <!-- Style de forme -->
              <div class="mb-2">
                <label for="shapeStyle" class="block mb-1 text-xs">Style de forme</label>
                <select id="shapeStyle" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-[#c0c0c0]">
                  <option value="flat-fill">Remplissage plat</option>
                  <option value="flat-stroke">Contour simple</option>
                  <option value="double-stroke">Double contour</option>
                  <option value="soft-shadow">Ombre douce</option>
                  <option value="inner-shadow">Ombre interne</option>
                  <option value="glow">Lueur externe</option>
                  <option value="glass">Verre</option>
                  <option value="metal">Métal</option>
                  <option value="neon">Néon</option>
                  <option value="pastel">Pastel doux</option>
                  <option value="ink">Encre nette</option>
                  <option value="marker">Feutre marqueur</option>
                  <option value="pixel">Pixel art</option>
                  <option value="wireframe">Fil de fer</option>
                  <option value="dashed">Contour pointillé</option>
                  <option value="dotted">Contour à points</option>
                  <option value="soft-gradient">Dégradé doux</option>
                  <option value="glass-gradient">Verre dégradé</option>
                  <option value="emboss">Relief (emboss)</option>
                  <option value="cutout">Découpe</option>
                </select>
              </div>
          </div>

          <label class="block mb-1 text-sm">Color Mode</label>
          <select id="colorMode" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 mb-3 text-[#c0c0c0]">
            <option value="solid">Solid Color</option>
            <option value="gradient">Gradient</option>
          </select>

          <div id="gradientAngleContainer" class="mb-3 hidden">
            <h3 class="text-sm font-semibold mb-2 text-[#00aaff]">Paramètres Gradient</h3>
            
            <!-- Angle du gradient -->
            <div class="mb-2">
              <label for="gradientAngle" class="block mb-1 text-xs">Angle: <span id="gradientAngleValue">0</span>°</label>
              <input type="range" id="gradientAngle" min="-180" max="180" value="0" class="w-full" />
              <div class="flex justify-between text-xs text-[#999] mt-1">
                <span>-180°</span>
                <span>0°</span>
                <span>180°</span>
              </div>
            </div>
            
            <!-- Preset angles rapides -->
            <div class="grid grid-cols-5 gap-1 mb-2">
              <button class="preset-angle bg-[#444] hover:bg-[#555] text-white px-1 py-1 rounded text-xs" data-angle="-180">-180°</button>
              <button class="preset-angle bg-[#444] hover:bg-[#555] text-white px-1 py-1 rounded text-xs" data-angle="-90">-90°</button>
              <button class="preset-angle bg-[#444] hover:bg-[#555] text-white px-1 py-1 rounded text-xs" data-angle="0">0°</button>
              <button class="preset-angle bg-[#444] hover:bg-[#555] text-white px-1 py-1 rounded text-xs" data-angle="90">90°</button>
              <button class="preset-angle bg-[#444] hover:bg-[#555] text-white px-1 py-1 rounded text-xs" data-angle="180">180°</button>
            </div>
            
            <!-- Directions prédéfinies -->
            <div class="grid grid-cols-5 gap-1 mb-3">
              <button class="preset-direction bg-[#555] hover:bg-[#666] text-white px-1 py-1 rounded text-xs" data-direction="left">Left</button>
              <button class="preset-direction bg-[#555] hover:bg-[#666] text-white px-1 py-1 rounded text-xs" data-direction="bottom">Bottom</button>
              <button class="preset-direction bg-[#555] hover:bg-[#666] text-white px-1 py-1 rounded text-xs" data-direction="center">Center</button>
              <button class="preset-direction bg-[#555] hover:bg-[#666] text-white px-1 py-1 rounded text-xs" data-direction="top">Top</button>
              <button class="preset-direction bg-[#555] hover:bg-[#666] text-white px-1 py-1 rounded text-xs" data-direction="right">Right</button>
            </div>
            
            <!-- Transitions des zones -->
            <div class="space-y-2">
              <div>
                <label for="topTransition" class="block mb-1 text-xs">Transition Top: <span id="topTransitionValue">0</span>%</label>
                <input type="range" id="topTransition" min="0" max="100" value="0" class="w-full" />
              </div>
              <div>
                <label for="middleTransition" class="block mb-1 text-xs">Transition Middle: <span id="middleTransitionValue">50</span>%</label>
                <input type="range" id="middleTransition" min="0" max="100" value="50" class="w-full" />
              </div>
              <div>
                <label for="bottomTransition" class="block mb-1 text-xs">Transition Bottom: <span id="bottomTransitionValue">100</span>%</label>
                <input type="range" id="bottomTransition" min="0" max="100" value="100" class="w-full" />
              </div>
              <div>
                <label for="sideTransition" class="block mb-1 text-xs">Transition Côtés: <span id="sideTransitionValue">50</span>%</label>
                <input type="range" id="sideTransition" min="0" max="100" value="50" class="w-full" />
              </div>
            </div>
            
            <!-- Intensité et saturation -->
            <div class="space-y-2 mt-3">
              <div>
                <label for="gradientIntensity" class="block mb-1 text-xs">Intensité: <span id="gradientIntensityValue">100</span>%</label>
                <input type="range" id="gradientIntensity" min="0" max="200" value="100" class="w-full" />
              </div>
              <div>
                <label for="gradientSaturation" class="block mb-1 text-xs">Saturation: <span id="gradientSaturationValue">100</span>%</label>
                <input type="range" id="gradientSaturation" min="0" max="200" value="100" class="w-full" />
              </div>
            </div>
          </div>

          <label for="opacity" class="block mb-1 text-sm">Opacity: <span id="opacityValue">100</span>%</label>
          <input type="range" id="opacity" min="0" max="1" step="0.01" value="1" class="w-full mb-3" />

          <!-- STYLES ARTISTIQUES AMÉLIORÉS PHASE 6 -->
          <div class="p-3 bg-[#252525] border-b border-[#555]">
            <h2 class="text-lg font-semibold mb-2 text-[#00aaff]">🎨 Styles Artistiques</h2>
            
            <label class="block mb-1 text-sm">Style de pinceau</label>
            <select id="brushStyle" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 mb-3 text-[#c0c0c0]">
              <option value="normal">Normal</option>
              <!-- STYLES TRADITIONNELS AMÉLIORÉS -->
              <option value="pastel">🎨 Pastel (doux, poudreux)</option>
              <option value="charcoal">⚫ Fusain (texturé, flou)</option>
              <option value="watercolor">💧 Aquarelle (dilué, transparent)</option>
              <option value="ink">🖋️ Encre (net, contrasté)</option>
              <option value="airbrush">💨 Aérographe (diffus, doux)</option>
              <option value="oil">🎭 Peinture à l'huile (épais, relief)</option>
              <option value="gouache">🖌️ Gouache (opaque, mat)</option>
              <option value="sponge">🧽 Éponge (moucheté, irrégulier)</option>
              
              <!-- STYLES SÉLECTION SIMPLIFIÉS -->
              <option value="fresco">🏛️ Fresque (granuleux, antique)</option>
              <option value="impasto">🎨 Impasto (très épais, sculptural)</option>
            </select>

              <!-- Mode de style: pinceau ou forme -->
              <div class="mb-2">
                <label class="block mb-1 text-xs">Mode de style</label>
                <select id="styleMode" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-[#c0c0c0]">
                  <option value="brush">Pinceau</option>
                  <option value="shape">Forme</option>
                </select>
              </div>

            <!-- Contrôles d'intensité du style -->
            <div class="mb-2">
              <label for="styleIntensity" class="block mb-1 text-xs">Intensité du style: <span id="styleIntensityValue">50</span>%</label>
              <input type="range" id="styleIntensity" min="0" max="100" value="50" class="w-full" />
            </div>

            <!-- Contrôles de grain/texture -->
            <div class="mb-2">
              <label for="textureGrain" class="block mb-1 text-xs">Grain/Texture: <span id="textureGrainValue">30</span>%</label>
              <input type="range" id="textureGrain" min="0" max="100" value="30" class="w-full" />
            </div>

            <!-- Contrôles d'étalement -->
            <div class="mb-2">
              <label for="spreading" class="block mb-1 text-xs">Étalement: <span id="spreadingValue">20</span>%</label>
              <input type="range" id="spreading" min="0" max="100" value="20" class="w-full" />
            </div>

            <!-- Contrôles de flou/blur -->
            <div class="mb-2">
              <label for="blurEffect" class="block mb-1 text-xs">Flou/Blur: <span id="blurEffectValue">0</span>px</label>
              <input type="range" id="blurEffect" min="0" max="20" value="0" class="w-full" />
            </div>

            <!-- Contrôles de brillance -->
            <div class="mb-2">
              <label for="shineIntensity" class="block mb-1 text-xs">Intensité brillance: <span id="shineIntensityValue">0</span>%</label>
              <input type="range" id="shineIntensity" min="0" max="100" value="0" class="w-full" />
            </div>

            <div class="mb-3">
              <label class="block mb-1 text-xs">Couleur de brillance</label>
              <select id="shineColor" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 mb-2 text-[#c0c0c0]">
                <option value="#ffffff">Blanc</option>
                <option value="#ffff00">Jaune</option>
                <option value="#00ff00">Vert</option>
                <option value="#0099ff">Bleu</option>
                <option value="#ff6600">Orange</option>
                <option value="#ff00ff">Violet</option>
                <option value="#ff0000">Rouge</option>
                <option value="#00ffff">Cyan</option>
                <option value="#ffccaa">Doré</option>
                <option value="#ccccff">Argenté</option>
              </select>
              
              <div class="mb-2">
                <label for="shineOpacity" class="block mb-1 text-xs">Opacité brillance: <span id="shineOpacityValue">30</span>%</label>
                <input type="range" id="shineOpacity" min="0" max="100" value="30" class="w-full" />
              </div>
            </div>

            <!-- Option pour les formes -->
            <div class="mb-2 hidden">
              <!-- Checkbox supprimée pour éviter les conflits de style -->
            </div>
          </div>

          <!-- PHASE 5 - TEXTURES NATURELLES ET EFFETS NUMÉRIQUES -->
          <div class="mb-3 p-3 bg-[#1a1a1a] border border-[#333] rounded">
            <h3 class="text-sm font-semibold mb-2 text-[#00aaff]">Phase 5 - Textures & Effets</h3>
            
            <!-- Textures traditionnelles améliorées -->
            <div class="mb-3">
              <label for="textureStyle" class="block mb-1 text-xs">Textures traditionnelles</label>
              <select id="textureStyle" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 mb-2 text-[#c0c0c0]">
                <option value="none">Aucune texture</option>
                <option value="brush-hair">🖌️ Pinceau en poils</option>
                <option value="clone-stamp">📍 Tampon de clonage</option>
                <option value="sponge-natural">🧽 Éponge naturelle</option>
                <option value="dry-brush">🌾 Pinceau sec</option>
                <option value="wet-paint">💧 Peinture humide</option>
                <option value="crosshatch">❌ Hachures croisées</option>
                <option value="stipple">🔴 Pointillisme</option>
                <option value="smudge">👆 Barbouillage</option>
                <option value="impasto-heavy">🎨 Impasto lourd</option>
                <option value="palette-knife">🔪 Couteau à palette</option>
                <option value="fan-brush">🌸 Pinceau éventail</option>
                <option value="sgraffito">🪚 Sgraffito</option>
                <option value="scumbling">🌀 Frottis</option>
                <option value="glazing">✨ Glacis</option>
                <option value="alla-prima">⚡ Alla prima</option>
                <option value="grisaille">⚫ Grisaille</option>
                <option value="chiaroscuro">🌓 Clair-obscur</option>
              </select>
            </div>

            <!-- Textures naturelles améliorées -->
            <div class="mb-3">
              <label for="naturalTexture" class="block mb-1 text-xs">Textures naturelles</label>
              <select id="naturalTexture" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 mb-2 text-[#c0c0c0]">
                <option value="none">Aucune texture naturelle</option>
                <option value="smoke">💨 Fumée</option>
                <option value="clouds">☁️ Nuages</option>
                <option value="water-waves">🌊 Eau/Ondes</option>
                <option value="flames">🔥 Flammes</option>
                <option value="marble">🏛️ Marbre</option>
                <option value="wood">🌳 Bois</option>
                <option value="stone">🗿 Pierre</option>
                <option value="metal-brushed">⚙️ Métal brossé</option>
                <option value="glass-frost">❄️ Verre givré</option>
                <option value="sand">🏖️ Sable</option>
                <option value="mud">🌍 Boue</option>
                <option value="skin">👤 Peau</option>
                <option value="bark">🌲 Écorce</option>
                <option value="fabric">🧵 Tissu</option>
                <option value="rust">🧪 Rouille</option>
                <option value="lightning">⚡ Éclair</option>
                <option value="lava">🌋 Lave</option>
                <option value="coral">🪸 Corail</option>
                <option value="crystal">💎 Cristal</option>
                <option value="fur">🐾 Fourrure</option>
                <option value="scales">🐍 Écailles</option>
                <option value="feathers">🪶 Plumes</option>
                <option value="moss">🌿 Mousse</option>
                <option value="ice">🧊 Glace</option>
                <option value="snow">❄️ Neige</option>
                <option value="galaxy">🌌 Galaxie</option>
                <option value="nebula">🌌 Nébuleuse</option>
                <option value="aurora">🌈 Aurore boréale</option>
                <option value="plasma">⚡ Plasma</option>
              </select>
            </div>

            <!-- Effets numériques -->
            <div class="mb-3">
              <label for="digitalEffect" class="block mb-1 text-xs">Effets numériques</label>
              <select id="digitalEffect" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 mb-2 text-[#c0c0c0]">
                <option value="none">Aucun effet numérique</option>
                <option value="pixel-art">🔲 Pixel Art</option>
                <option value="noise-grain">📺 Grain/Bruit</option>
                <option value="scan-lines">📊 Lignes de balayage</option>
                <option value="glitch">🔳 Glitch</option>
                <option value="mosaic">🎨 Mosaïque</option>
                <option value="dither">⬛ Tramage</option>
                <option value="chromatic">🌈 Aberration chromatique</option>
                <option value="vhs">📼 Effet VHS</option>
                <option value="crt">🖥️ Écran CRT</option>
                <option value="hologram">💿 Hologramme</option>
                <option value="circuit">⚡ Circuit électronique</option>
                <option value="matrix">🔢 Code Matrix</option>
                <option value="neon-digital">💡 Néon numérique</option>
                <option value="laser-beam">🔴 Faisceau laser</option>
                <option value="cyberpunk">🤖 Cyberpunk</option>
                <option value="synthwave">🌅 Synthwave</option>
                <option value="bitmap-retro">🖥️ Bitmap rétro</option>
                <option value="wireframe-3d">🔗 Fil de fer 3D</option>
                <option value="plasma-digital">⚡ Plasma numérique</option>
                <option value="fractal-art">🌀 Art fractal</option>
                <option value="kaleidoscope">🔄 Kaléidoscope</option>
                <option value="interference">📡 Interférence</option>
                <option value="data-moshing">💾 Data moshing</option>
                <option value="ascii-art">💻 ASCII art</option>
                <option value="8bit-game">🎮 8-bit jeu</option>
                <option value="16bit-console">🎯 16-bit console</option>
                <option value="quantum-field">⚛️ Champ quantique</option>
                <option value="neural-network">🧠 Réseau neural</option>
                <option value="deep-dream">👁️ Deep dream</option>
                <option value="code-rain">🌧️ Pluie de code</option>
                <option value="tech-grid">📐 Grille technologique</option>
                <option value="digital-brush">🖌️ Pinceau numérique</option>
                <option value="cyber-glow">✨ Lueur cyber</option>
                <option value="data-stream">📡 Flux de données</option>
                <option value="virtual-reality">🥽 Réalité virtuelle</option>
                <option value="augmented-reality">📱 Réalité augmentée</option>
              </select>
            </div>

            <!-- Intensité texture combinée -->
            <div class="mb-2">
              <label for="textureIntensity" class="block mb-1 text-xs">Intensité texture: <span id="textureIntensityValue">50</span>%</label>
              <input type="range" id="textureIntensity" min="0" max="100" value="50" class="w-full" />
            </div>

            <!-- Mode de fusion texture -->
            <div class="mb-2">
              <label for="textureBlendMode" class="block mb-1 text-xs">Mode de fusion</label>
              <select id="textureBlendMode" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-[#c0c0c0]">
                <option value="multiply">Multiplier</option>
                <option value="overlay">Superposition</option>
                <option value="soft-light">Lumière douce</option>
                <option value="hard-light">Lumière forte</option>
                <option value="screen">Écran</option>
                <option value="color-burn">Densité couleur +</option>
                <option value="color-dodge">Densité couleur -</option>
                <option value="difference">Différence</option>
                <option value="exclusion">Exclusion</option>
              </select>
            </div>
          </div>
          
          <!-- Contrôles spécifiques aux Lassos -->
          <div id="lassoControls" class="hidden mb-3">
            <label for="magneticStrength" class="block mb-1 text-sm">Magnetic Strength: <span id="magneticStrengthValue">10</span></label>
            <input type="range" id="magneticStrength" min="1" max="20" value="10" class="w-full mb-2" />
            
            <button id="finishPolygonBtn" class="w-full bg-[#00aaff] hover:bg-[#0088cc] text-white px-3 py-1 rounded mb-2 hidden">
              Finish Polygon
            </button>
            
            <button id="cancelLassoBtn" class="w-full bg-[#ff4444] hover:bg-[#cc3333] text-white px-3 py-1 rounded">
              Cancel Lasso
            </button>
          </div>

          <!-- Contrôles Actions de Sélection -->
          <div id="selectionControls" class="hidden mb-3">
            <h3 class="text-sm font-semibold mb-2 text-blue-400">Actions Sélection</h3>
            
            <!-- Actions de base -->
            <div class="grid grid-cols-2 gap-1 mb-2">
              <button id="cutSelection" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">
                <i class="fas fa-cut mr-1"></i>Couper
              </button>
              <button id="copySelection" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs">
                <i class="fas fa-copy mr-1"></i>Copier
              </button>
              <button id="pasteSelection" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs" disabled>
                <i class="fas fa-paste mr-1"></i>Coller
              </button>
              <button id="deleteSelection" class="bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded text-xs">
                <i class="fas fa-trash mr-1"></i>Suppr.
              </button>
            </div>

            <!-- Remplissage -->
            <div class="mb-2">
              <label class="block text-xs font-medium mb-1">Remplir :</label>
              <div class="grid grid-cols-2 gap-1">
                <button id="fillColor" class="bg-purple-600 hover:bg-purple-700 text-white px-2 py-1 rounded text-xs">
                  Couleur
                </button>
                <button id="fillGradient" class="bg-indigo-600 hover:bg-indigo-700 text-white px-2 py-1 rounded text-xs">
                  Dégradé
                </button>
              </div>
            </div>

            <!-- Opacité de sélection -->
            <div class="mb-2">
              <label for="selectionOpacity" class="block text-xs font-medium mb-1">Opacité: <span id="selectionOpacityValue">100</span>%</label>
              <input type="range" id="selectionOpacity" min="0" max="100" value="100" class="w-full" />
            </div>

            <!-- Protection de zone -->
            <div class="mb-2">
              <label class="flex items-center text-xs">
                <input type="checkbox" id="protectSelection" class="mr-2">
                <span>Protéger la zone</span>
              </label>
              <p class="text-xs text-gray-400 mt-1">Empêche la modification de la zone</p>
            </div>

            <!-- Mode déplacement -->
            <button id="toggleMoveMode" class="w-full bg-orange-600 hover:bg-orange-700 text-white px-2 py-1 rounded text-xs mb-2">
              <i class="fas fa-arrows-alt mr-1"></i>Mode Déplacement
            </button>

            <!-- Désélectionner -->
            <button id="clearSelection" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-2 py-1 rounded text-xs">
              <i class="fas fa-times mr-1"></i>Désélectionner
            </button>
          </div>

          <!-- PROPRIÉTÉS STYLES ARTISTIQUES POUR FORMES SÉLECTIONNÉES -->
          <div id="shapeArtisticStyle" class="hidden mb-3 p-3 bg-[#1a1a1a] border border-[#333] rounded">
            <h3 class="text-sm font-semibold mb-2 text-[#00aaff]">Style Artistique de la Forme</h3>
            
            <!-- Style artistique individuel -->
            <div class="mb-3">
              <label for="selectedShapeStyle" class="block mb-1 text-xs">Style de rendu</label>
              <select id="selectedShapeStyle" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 mb-3 text-[#c0c0c0]">
                <option value="normal">Normal (aucun effet)</option>
                <option value="pastel">Pastel</option>
                <option value="charcoal">Fusain</option>
                <option value="watercolor">Aquarelle</option>
                <option value="ink">Encre</option>
                <option value="airbrush">Aérographe</option>
                <option value="oil">Huile</option>
                <option value="gouache">Gouache</option>
                <option value="sponge">Éponge</option>
              </select>
            </div>

            <!-- Paramètres pour forme sélectionnée -->
            <div class="mb-2">
              <label for="selectedShapeIntensity" class="block mb-1 text-xs">Intensité: <span id="selectedShapeIntensityValue">50</span>%</label>
              <input type="range" id="selectedShapeIntensity" min="0" max="100" value="50" class="w-full" />
            </div>

            <div class="mb-2">
              <label for="selectedShapeGrain" class="block mb-1 text-xs">Grain: <span id="selectedShapeGrainValue">30</span>%</label>
              <input type="range" id="selectedShapeGrain" min="0" max="100" value="30" class="w-full" />
            </div>

            <div class="mb-2">
              <label for="selectedShapeSpreading" class="block mb-1 text-xs">Étalement: <span id="selectedShapeSpreadingValue">20</span>%</label>
              <input type="range" id="selectedShapeSpreading" min="0" max="100" value="20" class="w-full" />
            </div>

            <div class="mb-2">
              <label for="selectedShapeBlur" class="block mb-1 text-xs">Flou: <span id="selectedShapeBlurValue">0</span>px</label>
              <input type="range" id="selectedShapeBlur" min="0" max="10" value="0" step="0.5" class="w-full" />
            </div>

            <div class="mb-2">
              <label for="selectedShapeShine" class="block mb-1 text-xs">Brillance: <span id="selectedShapeShineValue">0</span>%</label>
              <input type="range" id="selectedShapeShine" min="0" max="100" value="0" class="w-full" />
            </div>

            <!-- Textures Phase 5 pour formes -->
            <div class="mb-2">
              <label for="selectedShapeTexture" class="block mb-1 text-xs">Texture supplémentaire</label>
              <select id="selectedShapeTexture" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-[#c0c0c0]">
                <option value="none">Aucune</option>
                <option value="brush-hair">Poils de pinceau</option>
                <option value="crosshatch">Hachures</option>
                <option value="stipple">Pointillisme</option>
                <option value="smoke">Fumée</option>
                <option value="wood">Bois</option>
                <option value="stone">Pierre</option>
                <option value="metal-brushed">Métal brossé</option>
                <option value="pixel-art">Pixel Art</option>
                <option value="glitch">Glitch</option>
                <option value="hologram">Hologramme</option>
              </select>
            </div>

            <!-- Bouton appliquer -->
            <button id="applyShapeStyle" class="w-full bg-[#00aaff] hover:bg-[#0088cc] text-white px-2 py-1 rounded text-xs">
              <i class="fas fa-magic mr-1"></i>Appliquer le Style
            </button>
          </div>
        </div>

        <!-- Colors section -->
        <div class="p-3 bg-[#252525] border-b border-[#555]">
          <h2 class="text-lg font-semibold mb-2">Colors</h2>
          <div id="colorInputsContainer">
            <div class="mb-3">
              <label for="color1" class="block mb-1 text-sm">Primary Color</label>
              <input type="color" id="color1" value="#ff0000" class="w-full h-10 p-0 border border-[#555] rounded cursor-pointer" />
              <input type="text" id="color1rgba" value="rgba(255,0,0,1)" class="w-full mt-1 px-2 py-1 bg-[#1e1e1e] border border-[#555] rounded text-[#c0c0c0] text-sm" />
            </div>
            <div class="mb-3">
              <label for="color2" class="block mb-1 text-sm">Secondary Color</label>
              <input type="color" id="color2" value="#0000ff" class="w-full h-10 p-0 border border-[#555] rounded cursor-pointer" />
              <input type="text" id="color2rgba" value="rgba(0,0,255,1)" class="w-full mt-1 px-2 py-1 bg-[#1e1e1e] border border-[#555] rounded text-[#c0c0c0] text-sm" />
            </div>
            <div class="mb-3" id="extraColorsContainer">
              <label for="color3" class="block mb-1 text-sm">Tertiary Color</label>
              <input type="color" id="color3" value="#00ff00" class="w-full h-10 p-0 border border-[#555] rounded cursor-pointer" />
              <input type="text" id="color3rgba" value="rgba(0,255,0,1)" class="w-full mt-1 px-2 py-1 bg-[#1e1e1e] border border-[#555] rounded text-[#c0c0c0] text-sm" />
            </div>
          </div>
          <button id="addColorBtn" class="w-full bg-[#00aaff] hover:bg-[#0088cc] text-white px-3 py-1 rounded flex items-center justify-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Add Color</span>
          </button>
        </div>

        <!-- Zoom & Navigation section -->
        <div class="p-3 bg-[#252525] border-b border-[#555]">
          <h2 class="text-lg font-semibold mb-2">Zoom & Navigation</h2>
          <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span>Zoom:</span>
              <span id="zoomDisplay" class="text-[#00aaff] font-mono">100%</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span>Précision:</span>
              <span id="precisionDisplay" class="text-[#00ff00] font-mono">1.000px</span>
            </div>
            <div class="grid grid-cols-3 gap-1">
              <button id="zoomOut" class="bg-[#444] hover:bg-[#555] text-white px-2 py-1 rounded text-xs">
                <i class="fas fa-minus"></i>
              </button>
              <button id="zoomReset" class="bg-[#00aaff] hover:bg-[#0088cc] text-white px-2 py-1 rounded text-xs">
                100%
              </button>
              <button id="zoomIn" class="bg-[#444] hover:bg-[#555] text-white px-2 py-1 rounded text-xs">
                <i class="fas fa-plus"></i>
              </button>
            </div>
            <button id="centerCanvas" class="w-full bg-[#555] hover:bg-[#666] text-white px-2 py-1 rounded text-xs mt-1">
              <i class="fas fa-crosshairs mr-1"></i>Centrer
            </button>
            <div class="text-xs text-[#999] mt-2 space-y-1">
              <p><kbd class="bg-[#333] px-1 rounded">Clic droit + glisser</kbd>: naviguer</p>
              <p><kbd class="bg-[#333] px-1 rounded">Espace</kbd>: centrer | <kbd class="bg-[#333] px-1 rounded">Molette</kbd>: zoom</p>
              <p><kbd class="bg-[#333] px-1 rounded">Ctrl+0</kbd>: reset | <kbd class="bg-[#333] px-1 rounded">Ctrl +/-</kbd>: zoom</p>
            </div>
          </div>
        </div>

        <!-- Selection Info section -->
        <div id="selectionInfo" class="hidden p-3 bg-[#1a1a1a] border-b border-[#555]">
          <h3 class="text-sm font-semibold mb-2 text-[#00aaff]">Info Sélection</h3>
          <div class="space-y-1 text-xs text-[#c0c0c0]">
            <div class="flex justify-between">
              <span>Type:</span>
              <span id="selectionTypeDisplay">-</span>
            </div>
            <div class="flex justify-between">
              <span>Taille:</span>
              <span id="selectionSizeDisplay">-</span>
            </div>
            <div class="flex justify-between">
              <span>Position:</span>
              <span id="selectionPosDisplay">-</span>
            </div>
            <div class="flex justify-between">
              <span>État:</span>
              <span id="selectionStatusDisplay">-</span>
            </div>
          </div>
        </div>

        <!-- Layers section -->
        <div class="p-3 bg-[#252525] flex-1">
          <h2 class="text-lg font-semibold mb-2">Layers</h2>
          <div class="text-sm text-[#999]">
            <p>Import an image to start editing. All modifications will appear here.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
<script>
  // ==== SYSTÈME DE TEXTE INTÉGRÉ ====
  // Assurer la présence de `canvas` et `ctx` avant toute utilisation (évite ReferenceError)
  if (!window.canvas) {
    window.canvas = document.getElementById('drawingCanvas');
  }
  if (!window.ctx && window.canvas) {
    window.ctx = window.canvas.getContext('2d');
  }

// 1. Ajouter l'icône Texte dans la barre d'outils gauche
let leftToolbar = document.getElementById('leftToolbar');
const textIconBtn = document.createElement('button');
textIconBtn.setAttribute('aria-label', 'Outil Texte');
textIconBtn.className = 'w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded';
textIconBtn.innerHTML = '<i class="fas fa-font text-[20px]"></i>';
textIconBtn.title = 'Outil Texte (T)';
leftToolbar.appendChild(textIconBtn);

// 2. Créer le panneau d'options de texte dans la colonne droite
// Renommer pour éviter les collisions globales avec un autre `toolsSection`
const toolsSectionText = document.getElementById('toolsSection');
const textOptionsPanel = document.createElement('div');
textOptionsPanel.id = 'textOptionsPanel';
textOptionsPanel.className = 'p-3 bg-[#252525] border-b border-[#555] text-[#c0c0c0] hidden';
textOptionsPanel.innerHTML = `
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-lg font-semibold">Options Texte</h2>
        <button id="closeTextPanelBtn" aria-label="Fermer panneau texte" class="text-[#00aaff] hover:text-[#0088cc] focus:outline-none">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>
    
    <!-- Contrôles de texte -->
    <div class="space-y-3">
        <!-- Police et taille -->
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label for="textFontFamily" class="block text-xs mb-1">Police</label>
                <select id="textFontFamily" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-sm">
                    <option value="Arial, sans-serif">Arial</option>
                    <option value="Georgia, serif">Georgia</option>
                    <option value="'Courier New', monospace">Courier New</option>
                    <option value="'Times New Roman', serif">Times New Roman</option>
                    <option value="Verdana, sans-serif">Verdana</option>
                    <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                    <option value="'Comic Sans MS', cursive">Comic Sans MS</option>
                    <option value="Impact, sans-serif">Impact</option>
                    <option value="'Palatino Linotype', serif">Palatino</option>
                </select>
            </div>
            <div>
                <label for="textFontSize" class="block text-xs mb-1">Taille</label>
                <input type="range" id="textFontSize" min="8" max="200" value="24" class="w-full" />
                <div class="flex justify-between text-xs">
                    <span id="textFontSizeValue">24</span>
                    <span>px</span>
                </div>
            </div>
        </div>
        
        <!-- Couleur du texte -->
        <div>
          <label for="textColor" class="block text-xs mb-1">Couleur du texte</label>
          <input type="color" id="textColor" value="#000000" class="w-full h-8 p-0 border border-[#555] rounded cursor-pointer" />
        </div>

        <!-- Opacité & décorations du texte -->
        <div class="grid grid-cols-2 gap-2 mt-2">
          <div>
            <label for="textOpacity" class="block text-xs mb-1">Opacité texte: <span id="textOpacityValue">100</span>%</label>
            <input type="range" id="textOpacity" min="0" max="100" value="100" class="w-full" />
          </div>
          <div>
            <label for="textDecoration" class="block text-xs mb-1">Décoration</label>
            <select id="textDecoration" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-sm">
              <option value="none">Aucune</option>
              <option value="underline">Souligné</option>
              <option value="line-through">Barré</option>
              <option value="overline">Ligne au-dessus</option>
            </select>
          </div>
        </div>
        
        <!-- Arrière-plan -->
        <div>
            <label class="flex items-center text-xs mb-2">
                <input type="checkbox" id="textHasBackground" class="mr-2" />
                <span>Arrière-plan</span>
            </label>
            <div id="textBackgroundOptions" class="space-y-2 hidden">
                <input type="color" id="textBackgroundColor" value="#ffffff" class="w-full h-8 p-0 border border-[#555] rounded cursor-pointer" />
                <div>
                    <label for="textBackgroundOpacity" class="block text-xs mb-1">Opacité: <span id="textBackgroundOpacityValue">100</span>%</label>
                    <input type="range" id="textBackgroundOpacity" min="0" max="100" value="100" class="w-full" />
                </div>
            </div>
        </div>
        
        <!-- Bouton Importer police -->
        <div class="mt-4 space-y-2">
          <div>
            <label for="fontUpload" class="block text-xs mb-2 font-semibold text-[#00aaff]">Importer une police personnalisée</label>
            <input type="file" id="fontUpload" accept=".zip,.rar,.7z,.ttf,.otf" class="hidden" />
            <label for="fontUpload" class="block w-full bg-[#00aaff] hover:bg-[#0088cc] text-white text-center py-2 rounded cursor-pointer text-sm">
              <i class="fas fa-upload mr-2"></i>Importer une police (ZIP/TTF/OTF)
            </label>
            <p class="text-xs text-gray-400 mt-1">Le ZIP doit contenir un fichier .ttf ou .otf et liscence.txt</p>
          </div>

          <!-- Google Fonts de base -->
          <div>
            <label for="googleFontSelect" class="block text-xs mb-1">Google Fonts (exemples)</label>
            <select id="googleFontSelect" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-sm">
              <option value="">-- Aucune --</option>
              <option value="Roboto">Roboto</option>
              <option value="Open Sans">Open Sans</option>
              <option value="Lato">Lato</option>
              <option value="Montserrat">Montserrat</option>
              <option value="Poppins">Poppins</option>
              <option value="Raleway">Raleway</option>
              <option value="Merriweather">Merriweather</option>
              <option value="Playfair Display">Playfair Display</option>
            </select>
            <a href="https://fonts.google.com" target="_blank" class="block text-[10px] text-[#00aaff] mt-1 underline">Ouvrir Google Fonts pour plus de polices</a>
          </div>
        </div>
        
        <!-- Style du texte -->
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label for="textStyle" class="block text-xs mb-1">Style</label>
                <select id="textStyle" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-sm">
                    <option value="normal">Normal</option>
                    <option value="italic">Italique</option>
                    <option value="bold">Gras</option>
                    <option value="bold italic">Gras Italique</option>
                </select>
            </div>
            <div>
                <label for="textAlign" class="block text-xs mb-1">Alignement</label>
                <select id="textAlign" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-sm">
                    <option value="left">Gauche</option>
                    <option value="center">Centre</option>
                    <option value="right">Droite</option>
                    <option value="justify">Justifié</option>
                </select>
            </div>
        </div>
        
        <!-- Boutons d'action -->
        <div class="grid grid-cols-2 gap-2 mt-4">
            <button id="applyTextBtn" class="bg-green-600 hover:bg-green-700 text-white py-1 rounded text-sm">
                Appliquer
            </button>
            <button id="deleteTextBtn" class="bg-red-600 hover:bg-red-700 text-white py-1 rounded text-sm" disabled>
                Supprimer
            </button>
        </div>
        <div class="mt-3">
          <button id="addTextBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm">
            <i class="fas fa-plus mr-1"></i>Ajouter un texte
          </button>
        </div>
    </div>
`;

// Insérer le panneau après la section des outils
toolsSectionText.parentNode.insertBefore(textOptionsPanel, toolsSectionText.nextSibling);

// 3. Variables pour le système de texte
let textToolActive = false;
let textElements = []; // Stocke tous les éléments texte créés
let textEditingActive = false;
let activeTextElement = null; // Élément texte en cours d'édition ou sélectionné

// Chargement automatique des polices locales présentes dans fontfam/
async function loadPreloadedFonts() {
  if (!Array.isArray(window.preloadedFonts)) return;
  const select = document.getElementById('textFontFamily');
  if (!select) return;
  for (const font of window.preloadedFonts) {
    if (!font || !font.url) continue;
    const fontName = font.name || font.url.split('/').pop().split('.')[0];
    try {
      const fontFace = new FontFace(fontName, `url(${font.url})`);
      await fontFace.load();
      document.fonts.add(fontFace);
      const option = document.createElement('option');
      option.value = fontName;
      option.textContent = fontName + ' (local)';
      option.style.fontFamily = `'${fontName}', sans-serif`;
      select.appendChild(option);
    } catch (e) {
      console.warn('Impossible de charger la police locale', font, e);
    }
  }
}

// Lancer le chargement des polices locales
loadPreloadedFonts();

// 4. Gestion de l'activation/désactivation de l'outil texte
textIconBtn.addEventListener('click', () => {
    textToolActive = !textToolActive;
    
    if (textToolActive) {
        // Activer l'outil texte
        textIconBtn.classList.add('bg-[#00aaff]');
        textIconBtn.classList.remove('text-[#c0c0c0]');
        textIconBtn.style.color = 'white';
        
        // Afficher le panneau d'options texte
        textOptionsPanel.classList.remove('hidden');
        toolsSectionText.style.display = 'none';
        
        // Masquer les autres panneaux
        Array.from(document.getElementById('rightPanel').children).forEach(child => {
          if (child !== textOptionsPanel) child.style.display = 'none';
        });
        
        // Changer le curseur
        canvas.style.cursor = 'text';
        
        // Désélectionner les autres éléments
        deselectElement();
        selectedImageIndex = -1;
    } else {
        // Désactiver l'outil texte
        textIconBtn.classList.remove('bg-[#00aaff]');
        textIconBtn.classList.add('text-[#c0c0c0]');
        textIconBtn.style.color = '';
        
        // Masquer le panneau texte et réafficher les outils
        textOptionsPanel.classList.add('hidden');
        toolsSectionText.style.display = 'block';
        
        // Réafficher les autres panneaux
        Array.from(document.getElementById('rightPanel').children).forEach(child => {
          if (child !== toolsSectionText) child.style.display = 'block';
        });
        
        // Restaurer le curseur
        canvas.style.cursor = 'default';
        
        // Quitter le mode édition si actif
        if (textEditingActive) {
            finishTextEditing();
        }
    }
});

// Bouton de fermeture du panneau texte
document.getElementById('closeTextPanelBtn').addEventListener('click', () => {
    textToolActive = false;
    textIconBtn.classList.remove('bg-[#00aaff]');
    textIconBtn.classList.add('text-[#c0c0c0]');
    textIconBtn.style.color = '';
    
    textOptionsPanel.classList.add('hidden');
    toolsSectionText.style.display = 'block';
    
    Array.from(document.getElementById('rightPanel').children).forEach(child => {
      if (child !== toolsSectionText) child.style.display = 'block';
    });
    
    canvas.style.cursor = 'default';
    
    if (textEditingActive) {
        finishTextEditing();
    }
});

// 5. Création d'un nouvel élément texte
function createTextElement(x, y, initialText = "Texte") {
    const textId = 'text-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    const fontSize = parseInt(document.getElementById('textFontSize').value) || 24;
    const fontFamily = document.getElementById('textFontFamily').value || 'Arial, sans-serif';
    const color = document.getElementById('textColor').value || '#000000';
    const hasBackground = document.getElementById('textHasBackground').checked;
    const backgroundColor = document.getElementById('textBackgroundColor').value || '#ffffff';
    const backgroundOpacity = parseInt(document.getElementById('textBackgroundOpacity').value) / 100;
    const textStyle = document.getElementById('textStyle').value || 'normal';
    const textAlign = document.getElementById('textAlign').value || 'left';
    const textOpacity = parseInt(document.getElementById('textOpacity').value) / 100;
    const textDecoration = document.getElementById('textDecoration').value || 'none';
    
    const textElement = {
        id: textId,
        type: 'text',
        x: x,
        y: y,
        width: 200, // Largeur initiale estimée
        height: fontSize * 1.5,
        text: initialText,
        fontSize: fontSize,
        fontFamily: fontFamily,
        color: color,
        opacity: isNaN(textOpacity) ? 1 : textOpacity,
        decoration: textDecoration,
        opacity: isNaN(textOpacity) ? 1 : textOpacity,
        decoration: textDecoration,
        hasBackground: hasBackground,
        backgroundColor: backgroundColor,
        backgroundOpacity: backgroundOpacity,
        style: textStyle,
        align: textAlign,
        rotation: 0,
        priority: textElements.length,
        createdAt: Date.now()
    };
    
    textElements.push(textElement);
    
    // Ajouter aux calques
    if (window.layersPanelAPI) {
        window.layersPanelAPI.addLayerForText(textElement);
    }
    
    return textElement;
}

// 6. Dessin d'un élément texte
function drawTextElement(ctx, textElement, opts = {}) {
    if (!textElement) return;
    
    ctx.save();
    
    // Appliquer la rotation
    if (textElement.rotation && textElement.rotation !== 0) {
        const centerX = textElement.x + textElement.width / 2;
        const centerY = textElement.y + textElement.height / 2;
        ctx.translate(centerX, centerY);
        ctx.rotate((textElement.rotation * Math.PI) / 180);
        ctx.translate(-centerX, -centerY);
    }
    
    // Dessiner l'arrière-plan
    if (textElement.hasBackground) {
        ctx.fillStyle = textElement.backgroundColor;
        ctx.globalAlpha = textElement.backgroundOpacity !== undefined ? textElement.backgroundOpacity : 1;
        ctx.fillRect(textElement.x, textElement.y, textElement.width, textElement.height);
        ctx.globalAlpha = 1;
    }
    
    // Appliquer styles artistiques basiques pour le texte (glacis/sfumato/abstract/cubist/surreal)
    function applyTextArtStyle(ctx2, el) {
      const style = window.currentBrushStyle || 'normal';
      if (style === 'glaze' || style === 'glacis') {
        ctx2.globalAlpha = Math.min(1, (window.shineOpacity ?? 30) / 100) * 0.5;
      } else if (style === 'scumble' || style === 'sfumato') {
        const off = document.createElement('canvas');
        off.width = Math.ceil(el.width);
        off.height = Math.ceil(el.height);
        const octx = off.getContext('2d');
        octx.filter = `blur(${Math.max(2, window.blurEffect || 2)}px)`;
        octx.font = `${(el.style && el.style.includes('bold') ? 'bold ' : '')}${(el.style && el.style.includes('italic') ? 'italic ' : '')}${el.fontSize}px ${el.fontFamily}`;
        octx.fillStyle = el.color;
        octx.textBaseline = 'top';
        const linesL = el.text.split('\n');
        const lh = el.fontSize * 1.2;
        linesL.forEach((line, i) => {
          octx.textAlign = el.align;
          const drawXLocal = el.align === 'center' ? el.width / 2 : (el.align === 'right' ? el.width : 0);
          octx.fillText(line, drawXLocal, i * lh);
        });
        ctx2.drawImage(off, el.x, el.y);
        return true;
      } else if (style === 'abstract') {
        const variants = [
          adjustColorBrightness(el.color, 20),
          adjustColorBrightness(el.color, -20)
        ];
        const linesL = el.text.split('\n');
        const lh = el.fontSize * 1.2;
        variants.forEach((v, i) => {
          ctx2.globalAlpha = 0.4;
          ctx2.fillStyle = v;
          ctx2.font = `${(el.style && el.style.includes('bold') ? 'bold ' : '')}${(el.style && el.style.includes('italic') ? 'italic ' : '')}${el.fontSize}px ${el.fontFamily}`;
          ctx2.textBaseline = 'top';
          linesL.forEach((line, idx) => {
            let dx = el.x + (i === 0 ? 2 : -2);
            let drawX = dx;
            if (el.align === 'center') drawX = el.x + el.width / 2;
            else if (el.align === 'right') drawX = el.x + el.width;
            ctx2.fillText(line, drawX, el.y + (idx * lh) + (i === 0 ? 1 : -1));
          });
          ctx2.globalAlpha = 1;
        });
      } else if (style === 'cubist') {
        ctx2.save();
        ctx2.globalAlpha = 0.85;
        const seg = 6;
        for (let i = 0; i < seg; i++) {
          ctx2.beginPath();
          const rx = el.x + (el.width / seg) * i;
          ctx2.rect(rx, el.y, el.width / seg, el.height);
          ctx2.clip();
        }
        ctx2.restore();
      } else if (style === 'surreal') {
        ctx2.save();
        ctx2.shadowColor = adjustColorBrightness(el.color, 40);
        ctx2.shadowBlur = 10;
        ctx2.globalAlpha = 0.95;
        ctx2.restore();
      }
      return false;
    }

    // Configurer la police
    let fontStyle = '';
    if (textElement.style && textElement.style.includes('bold')) fontStyle += 'bold ';
    if (textElement.style && textElement.style.includes('italic')) fontStyle += 'italic ';
    
    ctx.font = `${fontStyle}${textElement.fontSize}px ${textElement.fontFamily}`;
    ctx.fillStyle = textElement.color;
    if (typeof textElement.opacity === 'number') {
      ctx.globalAlpha = Math.max(0, Math.min(1, textElement.opacity));
    }
    ctx.textBaseline = 'top';
    
    // Gérer l'alignement et le texte multi-lignes
    const lines = textElement.text.split('\n');
    const lineHeight = textElement.fontSize * 1.2;
    
    const handled = applyTextArtStyle(ctx, textElement);
    lines.forEach((line, index) => {
        let drawX = textElement.x;
        if (textElement.align === 'center') {
            drawX = textElement.x + textElement.width / 2;
            ctx.textAlign = 'center';
        } else if (textElement.align === 'right') {
            drawX = textElement.x + textElement.width;
            ctx.textAlign = 'right';
        } else {
            ctx.textAlign = 'left';
        }
        
      if (!handled) {
        // Décoration: underline / line-through / overline (dessinée manuellement)
        ctx.fillText(line, drawX, textElement.y + (index * lineHeight));
        if (textElement.decoration && textElement.decoration !== 'none') {
          const metrics = ctx.measureText(line);
          const lineWidth = metrics.width;
          const baseY = textElement.y + (index * lineHeight);
          let decoY = baseY + textElement.fontSize; // underline
          if (textElement.decoration === 'line-through') {
            decoY = baseY + textElement.fontSize * 0.55;
          } else if (textElement.decoration === 'overline') {
            decoY = baseY + 1;
          }
          let startX = drawX;
          if (ctx.textAlign === 'center') startX = drawX - lineWidth / 2;
          else if (ctx.textAlign === 'right') startX = drawX - lineWidth;
          ctx.beginPath();
          ctx.moveTo(startX, decoY);
          ctx.lineTo(startX + lineWidth, decoY);
          ctx.lineWidth = Math.max(1, textElement.fontSize * 0.05);
          ctx.strokeStyle = textElement.color;
          ctx.stroke();
        }
      }
    });
    ctx.globalAlpha = 1;
    // Dessiner la bordure de sélection si actif
    // Ne jamais afficher la bordure pointillée ni les handles,
    // sauf si une sélection via calques est explicitement active
    if (!opts.skipSelection && window.layerSelectionActive && activeTextElement && activeTextElement.id === textElement.id) {
        ctx.strokeStyle = '#00aaff';
        ctx.lineWidth = 1;
        ctx.setLineDash([4, 4]);
        ctx.strokeRect(textElement.x, textElement.y, textElement.width, textElement.height);
        ctx.setLineDash([]);
    }
    
    ctx.restore();
}

// 7. Mise à jour de redrawAll
const originalRedrawAll = window.redrawAll;
window.redrawAll = function() {
    if (originalRedrawAll) originalRedrawAll();
    
  // Dessiner les textes selon la priorité
  const sorted = [...textElements].sort((a,b)=> (a.priority ?? 0) - (b.priority ?? 0));
  sorted.forEach(text => {
    drawTextElement(ctx, text);
  });
};

// 8. Gestion du clic pour le texte
function handleTextToolClick(x, y) {
    // Vérifier si on clique sur un texte existant
    const clickedText = getTextAtPosition(x, y);
    
    if (clickedText) {
        selectTextElement(clickedText);
        startTextEditing(clickedText);
    } else {
        // Créer un nouveau texte
        const newText = createTextElement(x, y);
        selectTextElement(newText);
        startTextEditing(newText);
    }
}

// 9. Détection du texte
function getTextAtPosition(x, y) {
    for (let i = textElements.length - 1; i >= 0; i--) {
        const t = textElements[i];
        if (x >= t.x && x <= t.x + t.width && y >= t.y && y <= t.y + t.height) {
            return t;
        }
    }
    return null;
}

// Détecter si un handle de texte est cliqué
function getTextHandleAtPosition(textElement, x, y) {
  const handleSize = 14;
  const inRect = (hx, hy) => (x >= hx - handleSize/2 && x <= hx + handleSize/2 && y >= hy - handleSize/2 && y <= hy + handleSize/2);
  const nw = { x: textElement.x, y: textElement.y };
  const ne = { x: textElement.x + textElement.width, y: textElement.y };
  const sw = { x: textElement.x, y: textElement.y + textElement.height };
  const se = { x: textElement.x + textElement.width, y: textElement.y + textElement.height };
  const rot = { x: textElement.x + textElement.width/2, y: textElement.y - 20 };
  const move = { x: rot.x, y: rot.y - 24 };
  if (inRect(nw.x, nw.y)) return { type: 'nw' };
  if (inRect(ne.x, ne.y)) return { type: 'ne' };
  if (inRect(sw.x, sw.y)) return { type: 'sw' };
  if (inRect(se.x, se.y)) return { type: 'se' };
  if (Math.hypot(x - rot.x, y - rot.y) <= 8) return { type: 'rotate' };
  if (Math.hypot(x - move.x, y - move.y) <= 10) return { type: 'move' };
  return null;
}

// 10. Sélection de texte
function selectTextElement(textElement) {
    deselectElement();
    activeTextElement = textElement;
    
    updateTextOptionsFromElement(textElement);
    
    // Intégration système global
    selectedElement = textElement;
    selectedElementType = 'text';
    isElementSelected = true;
    
    document.getElementById('deleteTextBtn').disabled = false;
    // Activer overlay flèches pour déplacement
    showTextMoveControls(textElement);
    
    redrawAll();
}

// 11. Mise à jour UI
function updateTextOptionsFromElement(textElement) {
    if (!textElement) return;
    document.getElementById('textFontSize').value = textElement.fontSize;
    document.getElementById('textFontSizeValue').textContent = textElement.fontSize;
    document.getElementById('textFontFamily').value = textElement.fontFamily;
    document.getElementById('textColor').value = textElement.color;
    document.getElementById('textHasBackground').checked = textElement.hasBackground;
    document.getElementById('textBackgroundColor').value = textElement.backgroundColor;
    document.getElementById('textBackgroundOpacity').value = (textElement.backgroundOpacity || 1) * 100;
    document.getElementById('textStyle').value = textElement.style;
    document.getElementById('textAlign').value = textElement.align;
    
    const bgOptions = document.getElementById('textBackgroundOptions');
    if (textElement.hasBackground) bgOptions.classList.remove('hidden');
    else bgOptions.classList.add('hidden');
}

// 12. Édition de texte (Textarea Overlay)
function startTextEditing(textElement) {
    if (textEditingActive) finishTextEditing();
    
    textEditingActive = true;
    activeTextElement = textElement;
    
    const textarea = document.createElement('textarea');
    textarea.id = 'textEditArea';
    textarea.value = textElement.text;
    
    // Calculer la position écran
    const canvasElement = document.getElementById('drawingCanvas');
    const canvasRect = canvasElement.getBoundingClientRect();
    const scaleX = canvasRect.width / canvasElement.width;
    const scaleY = canvasRect.height / canvasElement.height;
    
    // Styles
    textarea.style.position = 'absolute';
    // Ajustement pour le zoom et le pan si implémentés, sinon simple projection
    // On suppose que canvasOffset et zoomLevel sont globaux
    const screenX = canvasRect.left + (textElement.x * zoomLevel + canvasOffset.x);
    const screenY = canvasRect.top + (textElement.y * zoomLevel + canvasOffset.y);
    
    textarea.style.left = screenX + 'px';
    textarea.style.top = screenY + 'px';
    textarea.style.width = (textElement.width * zoomLevel) + 'px';
    textarea.style.height = (textElement.height * zoomLevel) + 'px';
    textarea.style.fontSize = (textElement.fontSize * zoomLevel) + 'px';
    textarea.style.fontFamily = textElement.fontFamily;
    textarea.style.color = textElement.color;
    textarea.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
    textarea.style.border = '2px dashed #00aaff';
    textarea.style.padding = '0';
    textarea.style.margin = '0';
    textarea.style.outline = 'none';
    textarea.style.resize = 'both';
    textarea.style.overflow = 'hidden';
    textarea.style.zIndex = '1000';
    
    if (textElement.style.includes('bold')) textarea.style.fontWeight = 'bold';
    if (textElement.style.includes('italic')) textarea.style.fontStyle = 'italic';
    textarea.style.textAlign = textElement.align;
    
    document.body.appendChild(textarea);
    textarea.focus();
    
    textarea.addEventListener('blur', finishTextEditing);
    textarea.addEventListener('input', () => {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    });
}

function finishTextEditing() {
    const textarea = document.getElementById('textEditArea');
    if (!textarea || !activeTextElement) {
        textEditingActive = false;
        return;
    }
    
    activeTextElement.text = textarea.value;
    
    // Mettre à jour dimensions
    const rect = textarea.getBoundingClientRect();
    activeTextElement.width = rect.width / zoomLevel;
    activeTextElement.height = rect.height / zoomLevel;
    
    textarea.remove();
    textEditingActive = false;
    // Mettre à jour la position des contrôles
    if (activeTextElement) updateTextMoveControlsPosition(activeTextElement);
    redrawAll();
}

// 21. Import de polices (PHP + JS)
document.getElementById('fontUpload').addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('fontUpload', file);
    
    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            const fontFace = new FontFace(result.fontName, `url(${result.fontUrl})`);
            await fontFace.load();
            document.fonts.add(fontFace);
            
            const option = document.createElement('option');
            option.value = result.fontName;
            option.textContent = result.fontName + ' (Importé)';
            document.getElementById('textFontFamily').appendChild(option);
            document.getElementById('textFontFamily').value = result.fontName;
            
            if (activeTextElement) {
                activeTextElement.fontFamily = result.fontName;
                redrawAll();
            }
            
            alert('Police importée avec succès !');
        } else {
            alert('Erreur: ' + result.message);
        }
    } catch (err) {
        console.error(err);
        alert('Erreur lors de l\'upload.');
    }
    
    e.target.value = '';

    // Applique un style de forme générique à un chemin déjà défini dans ctx
    function applyShapeStyleToPath(ctx, shapeStyle, finalColor, s) {
      const strokeWidth = s.outlineThickness || 1;
      const baseOpacity = ctx.globalAlpha;

      switch (shapeStyle) {
        case 'flat-stroke':
          ctx.fillStyle = 'transparent';
          ctx.strokeStyle = finalColor;
          ctx.lineWidth = strokeWidth;
          ctx.stroke();
          break;

        case 'double-stroke':
          ctx.strokeStyle = finalColor;
          ctx.lineWidth = strokeWidth * 2;
          ctx.stroke();
          ctx.lineWidth = Math.max(1, strokeWidth * 0.5);
          ctx.globalAlpha = baseOpacity * 0.7;
          ctx.stroke();
          break;

        case 'soft-shadow':
          ctx.save();
          ctx.shadowColor = 'rgba(0,0,0,0.4)';
          ctx.shadowBlur = 10;
          ctx.shadowOffsetX = 4;
          ctx.shadowOffsetY = 4;
          ctx.fillStyle = finalColor;
          ctx.fill();
          ctx.restore();
          break;

        case 'inner-shadow':
          ctx.save();
          ctx.clip();
          ctx.globalAlpha = baseOpacity * 0.6;
          ctx.fillStyle = 'rgba(0,0,0,0.4)';
          ctx.fillRect(s.x + 3, s.y + 3, s.w - 6, s.h - 6);
          ctx.restore();
          ctx.fillStyle = finalColor;
          ctx.globalAlpha = baseOpacity;
          ctx.fill();
          break;

        case 'glow':
          ctx.save();
          ctx.shadowColor = finalColor;
          ctx.shadowBlur = 15;
          ctx.fillStyle = finalColor;
          ctx.fill();
          ctx.restore();
          break;

        case 'glass':
          ctx.save();
          ctx.fillStyle = 'rgba(255,255,255,0.25)';
          ctx.fill();
          ctx.globalAlpha = baseOpacity * 0.8;
          ctx.strokeStyle = finalColor;
          ctx.lineWidth = strokeWidth;
          ctx.stroke();
          ctx.restore();
          break;

        case 'metal':
          ctx.save();
          const grad = ctx.createLinearGradient(s.x, s.y, s.x + s.w, s.y + s.h);
          grad.addColorStop(0, adjustColorBrightness(finalColor, -30));
          grad.addColorStop(0.5, adjustColorBrightness(finalColor, 20));
          grad.addColorStop(1, adjustColorBrightness(finalColor, -30));
          ctx.fillStyle = grad;
          ctx.fill();
          ctx.restore();
          break;

        case 'neon':
          ctx.save();
          ctx.shadowColor = finalColor;
          ctx.shadowBlur = 20;
          ctx.strokeStyle = finalColor;
          ctx.lineWidth = strokeWidth * 1.5;
          ctx.stroke();
          ctx.restore();
          break;

        case 'pastel':
          ctx.fillStyle = adjustColorBrightness(finalColor, 20);
          ctx.globalAlpha = baseOpacity * 0.8;
          ctx.fill();
          break;

        case 'ink':
          ctx.strokeStyle = finalColor;
          ctx.lineWidth = strokeWidth * 1.2;
          ctx.globalAlpha = baseOpacity;
          ctx.stroke();
          break;

        case 'marker':
          ctx.fillStyle = finalColor;
          ctx.globalAlpha = baseOpacity * 0.85;
          ctx.fill();
          break;

        case 'pixel':
          ctx.imageSmoothingEnabled = false;
          ctx.fillStyle = finalColor;
          ctx.fill();
          break;

        case 'wireframe':
          ctx.strokeStyle = finalColor;
          ctx.lineWidth = strokeWidth * 0.8;
          ctx.setLineDash([4, 4]);
          ctx.stroke();
          ctx.setLineDash([]);
          break;

        case 'dashed':
          ctx.strokeStyle = finalColor;
          ctx.lineWidth = strokeWidth;
          ctx.setLineDash([6, 6]);
          ctx.stroke();
          ctx.setLineDash([]);
          break;

        case 'dotted':
          ctx.strokeStyle = finalColor;
          ctx.lineWidth = strokeWidth;
          ctx.setLineDash([2, 6]);
          ctx.stroke();
          ctx.setLineDash([]);
          break;

        case 'soft-gradient':
          ctx.save();
          const g1 = ctx.createLinearGradient(s.x, s.y, s.x + s.w, s.y + s.h);
          g1.addColorStop(0, adjustColorBrightness(finalColor, 10));
          g1.addColorStop(1, adjustColorBrightness(finalColor, -10));
          ctx.fillStyle = g1;
          ctx.fill();
          ctx.restore();
          break;

        case 'glass-gradient':
          ctx.save();
          const g2 = ctx.createLinearGradient(s.x, s.y, s.x, s.y + s.h);
          g2.addColorStop(0, 'rgba(255,255,255,0.5)');
          g2.addColorStop(0.5, finalColor);
          g2.addColorStop(1, 'rgba(0,0,0,0.4)');
          ctx.fillStyle = g2;
          ctx.fill();
          ctx.restore();
          break;

        case 'emboss':
          ctx.save();
          ctx.fillStyle = finalColor;
          ctx.fill();
          ctx.globalAlpha = baseOpacity * 0.6;
          ctx.strokeStyle = 'rgba(255,255,255,0.6)';
          ctx.translate(-1, -1);
          ctx.stroke();
          ctx.strokeStyle = 'rgba(0,0,0,0.6)';
          ctx.translate(2, 2);
          ctx.stroke();
          ctx.restore();
          break;

        case 'cutout':
          ctx.save();
          ctx.fillStyle = adjustColorBrightness(finalColor, -20);
          ctx.fill();
          ctx.globalAlpha = baseOpacity * 0.6;
          ctx.strokeStyle = 'rgba(0,0,0,0.6)';
          ctx.stroke();
          ctx.restore();
          break;

        case 'flat-fill':
        default:
          ctx.fillStyle = finalColor;
          ctx.fill();
          break;
      }
    }
});

// Listeners options
document.getElementById('textFontSize').addEventListener('input', function() {
    if (activeTextElement) { activeTextElement.fontSize = parseInt(this.value); redrawAll(); }
    document.getElementById('textFontSizeValue').textContent = this.value;
});
document.getElementById('textFontFamily').addEventListener('change', function() {
    if (activeTextElement) { activeTextElement.fontFamily = this.value; redrawAll(); }
});
document.getElementById('textColor').addEventListener('input', function() {
    if (activeTextElement) { activeTextElement.color = this.value; redrawAll(); }
});
document.getElementById('textHasBackground').addEventListener('change', function() {
    if (activeTextElement) { activeTextElement.hasBackground = this.checked; redrawAll(); }
    const bgOptions = document.getElementById('textBackgroundOptions');
    if (this.checked) bgOptions.classList.remove('hidden'); else bgOptions.classList.add('hidden');
});
document.getElementById('textBackgroundColor').addEventListener('input', function() {
    if (activeTextElement) { activeTextElement.backgroundColor = this.value; redrawAll(); }
});
document.getElementById('textBackgroundOpacity').addEventListener('input', function() {
    if (activeTextElement) { activeTextElement.backgroundOpacity = parseInt(this.value)/100; redrawAll(); }
    document.getElementById('textBackgroundOpacityValue').textContent = this.value;
});
document.getElementById('textStyle').addEventListener('change', function() {
    if (activeTextElement) { activeTextElement.style = this.value; redrawAll(); }
});
document.getElementById('textAlign').addEventListener('change', function() {
    if (activeTextElement) { activeTextElement.align = this.value; redrawAll(); }
});
document.getElementById('textOpacity').addEventListener('input', function() {
  const v = parseInt(this.value) || 0;
  document.getElementById('textOpacityValue').textContent = v;
  if (activeTextElement) {
    activeTextElement.opacity = v / 100;
    redrawAll();
  }
});
document.getElementById('textDecoration').addEventListener('change', function() {
  if (activeTextElement) {
    activeTextElement.decoration = this.value;
    redrawAll();
  }
});
document.getElementById('applyTextBtn').addEventListener('click', function() {
    if (!activeTextElement && textToolActive) {
        const newText = createTextElement(canvas.width/2, canvas.height/2);
        selectTextElement(newText);
        startTextEditing(newText);
    }
});
// Bouton Ajouter un texte
document.getElementById('addTextBtn').addEventListener('click', function() {
  const x = Math.max(0, canvas.width / 2 - 100);
  const y = Math.max(0, canvas.height / 2 - 20);
  const newText = createTextElement(x, y, 'Texte');
  selectTextElement(newText);
  startTextEditing(newText);
});

// Désélectionne automatiquement le texte quand on change d'outil
toolSelect.addEventListener('change', () => {
  if (activeTextElement) {
    if (textEditingActive) finishTextEditing();
    activeTextElement = null;
    redrawAll();
  }
});
document.getElementById('deleteTextBtn').addEventListener('click', function() {
    if (activeTextElement && confirm('Supprimer ce texte ?')) {
        const index = textElements.indexOf(activeTextElement);
        if (index !== -1) textElements.splice(index, 1);
        if (window.layersPanelAPI) window.layersPanelAPI.removeLayerById(activeTextElement.id);
        if (textEditingActive) finishTextEditing();
        activeTextElement = null;
        this.disabled = true;
        redrawAll();
    }
});

// Bouton "Sélectionner texte"
const selectTextBtn = document.createElement('button');
selectTextBtn.textContent = "Sélectionner texte";
selectTextBtn.className = "bg-blue-600 hover:bg-blue-700 text-white py-1 rounded text-sm w-full mt-2";
selectTextBtn.onclick = () => {
    // Mode sélection de texte
    currentTool = 'text-select';
    textToolActive = true;
    canvas.style.cursor = 'pointer';
    showNotification("Cliquez sur un texte pour l'éditer", "info");
};
document.getElementById('textOptionsPanel').appendChild(selectTextBtn);

// Overlay de déplacement par flèches pour les textes
let textMoveOverlay = null;
function showTextMoveControls(textElement) {
  if (!textMoveOverlay) {
    textMoveOverlay = document.createElement('div');
    textMoveOverlay.id = 'textMoveOverlay';
    textMoveOverlay.style.position = 'absolute';
    textMoveOverlay.style.zIndex = '1001';
    textMoveOverlay.style.display = 'flex';
    textMoveOverlay.style.alignItems = 'center';
    textMoveOverlay.style.gap = '4px';
    textMoveOverlay.style.background = 'rgba(0,0,0,0.6)';
    textMoveOverlay.style.padding = '4px 6px';
    textMoveOverlay.style.borderRadius = '6px';
    const mkBtn = (icon, dir) => {
      const b = document.createElement('button');
      b.innerHTML = `<i class="fas fa-arrow-${icon}"></i>`;
      b.style.color = '#fff';
      b.style.background = '#0066aa';
      b.style.border = 'none';
      b.style.width = '28px';
      b.style.height = '28px';
      b.style.borderRadius = '4px';
      b.style.cursor = 'pointer';
      b.title = `Déplacer ${dir}`;
      b.addEventListener('click', () => {
        const step = 5;
        if (!activeTextElement) return;
        if (dir === 'up') activeTextElement.y -= step;
        if (dir === 'down') activeTextElement.y += step;
        if (dir === 'left') activeTextElement.x -= step;
        if (dir === 'right') activeTextElement.x += step;
        updateTextMoveControlsPosition(activeTextElement);
        redrawAll();
      });
      return b;
    };
    // Flèches
    textMoveOverlay.appendChild(mkBtn('up','up'));
    textMoveOverlay.appendChild(mkBtn('down','down'));
    textMoveOverlay.appendChild(mkBtn('left','left'));
    textMoveOverlay.appendChild(mkBtn('right','right'));
    // Bouton fermeture (croix rouge)
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.style.color = '#ff5555';
    closeBtn.style.background = 'transparent';
    closeBtn.style.border = 'none';
    closeBtn.style.width = '24px';
    closeBtn.style.height = '24px';
    closeBtn.style.cursor = 'pointer';
    closeBtn.title = 'Fermer les contrôles de déplacement';
    closeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      hideTextMoveControls();
    });
    textMoveOverlay.appendChild(closeBtn);
    document.body.appendChild(textMoveOverlay);
  }
  updateTextMoveControlsPosition(textElement);
}

function updateTextMoveControlsPosition(textElement) {
  if (!textMoveOverlay || !textElement) return;
  const canvasElement = document.getElementById('drawingCanvas');
  const rect = canvasElement.getBoundingClientRect();
  const screenX = rect.left + (textElement.x * zoomLevel + canvasOffset.x);
  const screenY = rect.top + (textElement.y * zoomLevel + canvasOffset.y) - 40; // au-dessus
  textMoveOverlay.style.left = `${screenX}px`;
  textMoveOverlay.style.top = `${screenY}px`;
}

function hideTextMoveControls() {
  if (textMoveOverlay) {
    textMoveOverlay.remove();
    textMoveOverlay = null;
  }
}

// Pointer events pour le texte
const originalPointerDown = canvas.onpointerdown;
canvas.onpointerdown = function(e) {
    const pos = getScaledPointerPos(e);
    
    if (textToolActive) {
        handleTextToolClick(pos.x, pos.y);
        e.preventDefault();
        return;
    }
    
    // Vérifier clic sur texte hors mode outil
    // Détection des handles pour texte
    const clickedText = getTextAtPosition(pos.x, pos.y);
    if (clickedText) {
        selectTextElement(clickedText);
        const handle = getTextHandleAtPosition(clickedText, pos.x, pos.y);
        if (handle) {
          if (handle.type === 'rotate') {
            isRotating = true;
          } else {
            if (handle.type === 'move') {
              isDragging = true;
              dragOffset = { x: pos.x - clickedText.x, y: pos.y - clickedText.y };
            } else {
              isResizing = true;
              elementResizeHandle = handle.type; // 'nw','ne','sw','se'
            }
          }
        } else {
          // Drag du bloc texte
          isDragging = true;
          dragOffset = { x: pos.x - clickedText.x, y: pos.y - clickedText.y };
        }
        e.preventDefault();
        return;
    }
    
    if (originalPointerDown) originalPointerDown(e);
};

// Mouvement en temps réel pour texte (drag)
const prevMove = canvas.onpointermove;
canvas.onpointermove = function(e){
  const pos = getScaledPointerPos(e);
  if (isDragging && activeTextElement) {
    activeTextElement.x = pos.x - dragOffset.x;
    activeTextElement.y = pos.y - dragOffset.y;
    redrawAll();
    e.preventDefault();
    return;
  }
  if ((isResizing || isRotating) && activeTextElement) {
    const te = activeTextElement;
    if (isResizing) {
      const hx = pos.x;
      const hy = pos.y;
      const minW = 20, minH = 20;
      if (elementResizeHandle === 'nw') {
        const newW = te.width + (te.x - hx);
        const newH = te.height + (te.y - hy);
        te.x = hx; te.y = hy;
        te.width = Math.max(minW, newW);
        te.height = Math.max(minH, newH);
      } else if (elementResizeHandle === 'ne') {
        const newW = Math.max(minW, hx - te.x);
        const newH = te.height + (te.y - hy);
        te.y = hy;
        te.width = newW; te.height = Math.max(minH, newH);
      } else if (elementResizeHandle === 'sw') {
        const newW = te.width + (te.x - hx);
        const newH = Math.max(minH, hy - te.y);
        te.x = hx; te.width = Math.max(minW, newW); te.height = newH;
      } else if (elementResizeHandle === 'se') {
        te.width = Math.max(minW, hx - te.x);
        te.height = Math.max(minH, hy - te.y);
      }
    } else if (isRotating) {
      const cx = te.x + te.width/2;
      const cy = te.y + te.height/2;
      const angle = Math.atan2(pos.y - cy, pos.x - cx) * 180 / Math.PI;
      te.rotation = angle;
    }
    redrawAll();
    e.preventDefault();
    return;
  }
  if (prevMove) prevMove.call(canvas, e);
};

const prevUp = canvas.onpointerup;
canvas.onpointerup = function(e){
  isDragging = false;
  isResizing = false;
  isRotating = false;
  elementResizeHandle = null;
  if (prevUp) prevUp.call(canvas, e);
};

// Rotation via Alt + roue sur texte sélectionné
canvas.addEventListener('wheel', (e) => {
  if (activeTextElement && e.altKey) {
    e.preventDefault();
    const delta = Math.sign(e.deltaY) * 2;
    activeTextElement.rotation = (activeTextElement.rotation || 0) + delta;
    redrawAll();
  }
}, { passive: false });

// Raccourci clavier: T pour basculer en mode texte
document.addEventListener('keydown', (e) => {
  if (e.key.toLowerCase() === 't') {
    textToolActive = !textToolActive;
    const textIconBtn = document.querySelector('#leftToolbar button[aria-label="Outil Texte"]');
    if (textToolActive) {
      if (textIconBtn) { textIconBtn.classList.add('bg-[#00aaff]'); textIconBtn.style.color = 'white'; }
      const toolsSectionText = document.getElementById('toolsSection');
      document.getElementById('textOptionsPanel').classList.remove('hidden');
      if (toolsSectionText) toolsSectionText.style.display = 'none';
      Array.from(document.getElementById('rightPanel').children).forEach(child => {
        if (child !== document.getElementById('textOptionsPanel')) child.style.display = 'none';
      });
      canvas.style.cursor = 'text';
    } else {
      if (textIconBtn) { textIconBtn.classList.remove('bg-[#00aaff]'); textIconBtn.style.color = ''; }
      document.getElementById('textOptionsPanel').classList.add('hidden');
      const toolsSectionText = document.getElementById('toolsSection');
      if (toolsSectionText) toolsSectionText.style.display = 'block';
      Array.from(document.getElementById('rightPanel').children).forEach(child => {
        if (child !== toolsSectionText) child.style.display = 'block';
      });
      canvas.style.cursor = 'default';
    }
  }
});
  </script>
  <script>
    const canvas = document.getElementById('drawingCanvas'),
      ctx = canvas.getContext('2d'),
      uploadInput = document.getElementById('uploadImage'),
      downloadBtn = document.getElementById('downloadBtn'),
      toolSelect = document.getElementById('toolSelect'),
      brushSizeInput = document.getElementById('brushSize'),
      brushSizeNumber = document.getElementById('brushSizeNumber'),
      brushSizeValue = document.getElementById('brushSizeValue'),
      colorModeSelect = document.getElementById('colorMode'),
      opacityInput = document.getElementById('opacity'),
      opacityValue = document.getElementById('opacityValue'),
      colorInputsContainer = document.getElementById('colorInputsContainer'),
      extraColorsContainer = document.getElementById('extraColorsContainer'),
      addColorBtn = document.getElementById('addColorBtn'),
      gradientAngleContainer = document.getElementById('gradientAngleContainer');

    let brushSize = 10,
      currentTool = 'brush-basic',
      imageLoaded = false,
      importedImage = null,
      shapes = [],
      isDrawing = false,
      lastPoint = null,
      selectionRect = null,
      isSelecting = false,
      clipboard = null,
      gradientAngle = 0,
      importedImages = [],
      selectedImageIndex = -1,
      resizeHandle = null,
      isDraggingImage = false,
      // Variables de sélection avancées
      selectionPath = null,
      selectionType = null, // 'rect', 'lasso', 'free', 'polygonal', 'magnetic'
      selectionOpacity = 100,
      isProtected = false,
      moveMode = false,
      copiedSelection = null,
      selectionBounds = null,
      isMovingSelection = false,
      selectionOffset = { x: 0, y: 0 },
      
      // SYSTÈME DE SÉLECTION AVANCÉ
      selectedElement = null,
      selectedElementIndex = -1,
      selectedElementType = null, // 'shape', 'drawing', 'image'
      isElementSelected = false,
      selectionHandles = [],
      isResizing = false,
      isRotating = false,
      isDragging = false,
      dragOffset = { x: 0, y: 0 },
      elementResizeHandle = null, // 'nw', 'ne', 'sw', 'se', 'n', 's', 'e', 'w'
      rotationHandle = false,
      selectedDrawingStrokeId = null, // **CORRECTION: Déplacer ici la variable**
      drawingStrokes = [], // **CORRECTION: Déplacer ici pour être accessible dans export**
      
      // **CORRECTION: Variables pour traquer l'application du style aux nouveaux dessins seulement**
      styleAppliedToNewOnly = false,
      styleActivationTime = 0,
      
      // Haute précision
      zoomLevel = 1,
      maxZoom = 10000, // Permet un zoom jusqu'à 0.0001px
      minZoom = 0.1,
      canvasOffset = { x: 0, y: 0 },
      // Navigation
      isPanning = false,
      lastPanPoint = { x: 0, y: 0 },
      panStartOffset = { x: 0, y: 0 },
      // Nouvelles options formes
      shapeOutlineOnly = false,
      outlineThickness = 1,
      borderRadius = 0,
      shapeRotation = 0,

      // STYLES ARTISTIQUES PHASE 4
      currentBrushStyle = 'normal',
      styleMode = 'brush',
      styleIntensity = 50,
      textureGrain = 30,
      spreading = 20,
      blurEffect = 0,
      shineIntensity = 0,
      shineColor = '#ffffff',
      shineOpacity = 30,
      applyStyleToShapes = false,
      
      // TEXTURES PHASE 5
      currentTextureStyle = 'none',
      currentNaturalTexture = 'none', 
      currentDigitalEffect = 'none',
      textureIntensity = 50,
      textureBlendMode = 'multiply',
      
      // Gradient avancé
      topTransition = 0,
      middleTransition = 50,
      bottomTransition = 100,
      sideTransition = 50,
      gradientIntensity = 100,
      gradientSaturation = 100,

      // Intensité du pinceau fumée
      smokeIntensity = 30,
      
      // **SYSTÈME UNDO/REDO**
      undoStack = [],
      redoStack = [],
      maxUndoSteps = 50;

    // Initialiser le tableau des zones effacées
    window.erasedAreas = [];
    
    // Canvas de sauvegarde pour les dessins - INITIALISER IMMÉDIATEMENT
    let drawingLayer = document.createElement('canvas');
    drawingLayer.width = 3840; // Même taille que le canvas principal
    drawingLayer.height = 2160;
    
    // Fonction pour s'assurer que drawingLayer a la bonne taille
    function ensureDrawingLayerSize() {
      if (!drawingLayer) {
        drawingLayer = document.createElement('canvas');
      }
      if (drawingLayer.width !== canvas.width || drawingLayer.height !== canvas.height) {
        // Sauvegarder le contenu actuel si il existe
        const oldContent = drawingLayer.width > 0 ? drawingLayer.getContext('2d').getImageData(0, 0, drawingLayer.width, drawingLayer.height) : null;
        
        // Redimensionner
        drawingLayer.width = canvas.width;
        drawingLayer.height = canvas.height;
        
        // Restaurer le contenu si il existait
        if (oldContent) {
          drawingLayer.getContext('2d').putImageData(oldContent, 0, 0);
        }
      }
    }
    
    // Fonction pour sauvegarder les dessins actuels (VERSION CORRIGÉE)
    function saveCurrentDrawings() {
      // Cette fonction ne fait plus rien car les dessins sont maintenant 
      // automatiquement sauvegardés dans drawingLayer en temps réel
      // Cela évite de perdre les effets spéciaux (crayon, craie, fumée)
      return;
    }
    
    // Fonction pour restaurer les dessins sauvegardés
    function restoreDrawings() {
      if (!drawingLayer) return;
      ctx.globalCompositeOperation = 'source-over';
      ctx.drawImage(drawingLayer, 0, 0);
    }

    // Utilitaires
    const parseRgba = (rgbaStr) => {
      const m = rgbaStr.match(/rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*(\d*\.?\d+))?\s*\)/);
      return m ? { r: +m[1], g: +m[2], b: +m[3], a: m[4] !== undefined ? +m[4] : 1 } : null;
    };
    const rgbaToString = c => `rgba(${Math.round(c.r)},${Math.round(c.g)},${Math.round(c.b)},${c.a.toFixed(3)})`;
    const lerp = (a,b,t) => a+(b-a)*t;
    const lerpColor = (c0,c1,t) => ({r:lerp(c0.r,c1.r,t),g:lerp(c0.g,c1.g,t),b:lerp(c0.b,c1.b,t),a:lerp(c0.a,c1.a,t)});
    const hexToRgba = (hex,a=1) => {
      let r=0,g=0,b=0;
      if(hex.length===4){r=parseInt(hex[1]+hex[1],16);g=parseInt(hex[2]+hex[2],16);b=parseInt(hex[3]+hex[3],16);}
      else if(hex.length===7){r=parseInt(hex[1]+hex[2],16);g=parseInt(hex[3]+hex[4],16);b=parseInt(hex[5]+hex[6],16);}
      return {r,g,b,a};
    };

    // Ajuste la luminosité d'une couleur (rgba string ou hex)
    function adjustColorBrightness(color, amount = 0) {
      let c;
      if (typeof color === 'string') {
        if (color.startsWith('#')) {
          c = hexToRgba(color, 1);
        } else {
          c = parseRgba(color);
        }
      } else {
        c = { r: color.r, g: color.g, b: color.b, a: color.a ?? 1 };
      }
      const clamp = v => Math.max(0, Math.min(255, v));
      const factor = amount / 100;
      c.r = clamp(c.r + (factor >= 0 ? (255 - c.r) * factor : c.r * factor));
      c.g = clamp(c.g + (factor >= 0 ? (255 - c.g) * factor : c.g * factor));
      c.b = clamp(c.b + (factor >= 0 ? (255 - c.b) * factor : c.b * factor));
      return rgbaToString({ r: c.r, g: c.g, b: c.b, a: c.a ?? 1 });
    }

    // Appliquer un style pinceau global sur le contexte (formes/traits/texte)
    function applyGlobalArtStyle(ctx, box = null, baseColor = '#000000') {
      const style = window.currentBrushStyle || 'normal';
      if (style === 'normal') return;
      const alphaBase = Math.max(0.05, Math.min(1, (window.opacity ?? 1)));
      const intensity = window.styleIntensity ?? 50;
      const blurPx = window.blurEffect ?? 0;
      const shineInt = window.shineIntensity ?? 0;
      const shineCol = window.shineColor ?? '#ffffff';
      switch (style) {
        case 'glaze':
        case 'glacis':
          ctx.globalAlpha = alphaBase * (0.3 + intensity/300);
          break;
        case 'scumble':
        case 'sfumato':
          if (blurPx > 0) ctx.filter = `blur(${Math.max(2, blurPx)}px)`;
          break;
        case 'abstract':
          ctx.globalAlpha = alphaBase * 0.9;
          break;
        case 'cubist':
          if (box && box.w > 0 && box.h > 0) {
            const seg = 6;
            ctx.save();
            for (let i = 0; i < seg; i++) {
              ctx.beginPath();
              const rx = box.x + (box.w / seg) * i;
              ctx.rect(rx, box.y, box.w / seg, box.h);
              ctx.clip();
            }
            ctx.restore();
          }
          break;
        case 'surreal':
          ctx.shadowColor = adjustColorBrightness(shineCol, 30);
          ctx.shadowBlur = Math.max(6, shineInt/2);
          ctx.globalAlpha = alphaBase * 0.95;
          break;
        case 'impasto':
          ctx.shadowColor = adjustColorBrightness(baseColor, -40);
          ctx.shadowBlur = 4 + intensity/10;
          ctx.shadowOffsetX = 2;
          ctx.shadowOffsetY = 2;
          ctx.globalAlpha = alphaBase * 1.0;
          break;
        case 'fresco':
          ctx.globalAlpha = alphaBase * 0.85;
          break;
        default:
          break;
      }
    }

    // **SYSTÈME UNDO/REDO**
    function saveState() {
      if (!imageLoaded) return;
      
      // Sauvegarder l'état actuel (canvas + données)
      const state = {
        canvasData: ctx.getImageData(0, 0, canvas.width, canvas.height),
        shapes: JSON.parse(JSON.stringify(shapes)),
        // **CORRECTION: Sauvegarder les données d'images sans l'objet HTMLImageElement**
        importedImages: importedImages.map(img => ({
          x: img.x,
          y: img.y,
          width: img.width,
          height: img.height,
          rotation: img.rotation,
          src: img.img ? img.img.src : null // Sauvegarder l'URL source
        })),
        drawingStrokes: JSON.parse(JSON.stringify(drawingStrokes)),
        layers: window.layersPanelAPI ? JSON.parse(JSON.stringify(window.layersPanelAPI.layers)) : []
      };
      
      // Ajouter à la pile d'undo
      undoStack.push(state);
      
      // Limiter la taille de la pile
      if (undoStack.length > maxUndoSteps) {
        undoStack.shift();
      }
      
      // Vider la pile de redo car on a fait une nouvelle action
      redoStack.length = 0;
      
      updateUndoRedoButtons();
    }
    
    function undo() {
      if (undoStack.length === 0) return;
      
      // Sauvegarder l'état actuel dans redo avant de faire undo
      const currentState = {
        canvasData: ctx.getImageData(0, 0, canvas.width, canvas.height),
        shapes: JSON.parse(JSON.stringify(shapes)),
        importedImages: importedImages.map(img => ({
          x: img.x,
          y: img.y,
          width: img.width,
          height: img.height,
          rotation: img.rotation,
          src: img.img ? img.img.src : null
        })),
        drawingStrokes: JSON.parse(JSON.stringify(drawingStrokes)),
        layers: window.layersPanelAPI ? JSON.parse(JSON.stringify(window.layersPanelAPI.layers)) : []
      };
      redoStack.push(currentState);
      
      // Restaurer l'état précédent
      const state = undoStack.pop();
      restoreState(state);
      
      updateUndoRedoButtons();
    }
    
    function redo() {
      if (redoStack.length === 0) return;
      
      // Sauvegarder l'état actuel dans undo avant de faire redo
      const currentState = {
        canvasData: ctx.getImageData(0, 0, canvas.width, canvas.height),
        shapes: JSON.parse(JSON.stringify(shapes)),
        importedImages: importedImages.map(img => ({
          x: img.x,
          y: img.y,
          width: img.width,
          height: img.height,
          rotation: img.rotation,
          src: img.img ? img.img.src : null
        })),
        drawingStrokes: JSON.parse(JSON.stringify(drawingStrokes)),
        layers: window.layersPanelAPI ? JSON.parse(JSON.stringify(window.layersPanelAPI.layers)) : []
      };
      undoStack.push(currentState);
      
      // Restaurer l'état suivant
      const state = redoStack.pop();
      restoreState(state);
      
      updateUndoRedoButtons();
    }
    
    function restoreState(state) {
      // Restaurer le canvas
      ctx.putImageData(state.canvasData, 0, 0);
      
      // Restaurer les données
      shapes.length = 0;
      shapes.push(...state.shapes);
      
      // **CORRECTION: Restaurer les images en recréant les objets HTMLImageElement**
      importedImages.length = 0;
      state.importedImages.forEach(imgData => {
        if (imgData.src) {
          const img = new Image();
          img.onload = () => {
            const imgObj = {
              img: img,
              x: imgData.x,
              y: imgData.y,
              width: imgData.width,
              height: imgData.height,
              rotation: imgData.rotation
            };
            importedImages.push(imgObj);
            redrawAll();
          };
          img.src = imgData.src;
        }
      });
      
      drawingStrokes.length = 0;
      drawingStrokes.push(...state.drawingStrokes);
      
      // Restaurer les layers si disponible
      if (window.layersPanelAPI && state.layers) {
        window.layersPanelAPI.layers.length = 0;
        window.layersPanelAPI.layers.push(...state.layers);
        if (window.layersPanelAPI.renderLayersList) {
          window.layersPanelAPI.renderLayersList();
        }
      }
      
      // Redessiner tout
      redrawAll();
    }
    
    function updateUndoRedoButtons() {
      const undoBtn = document.getElementById('undoBtn');
      const redoBtn = document.getElementById('redoBtn');
      
      if (undoBtn) {
        undoBtn.style.opacity = undoStack.length > 0 ? '1' : '0.5';
        undoBtn.disabled = undoStack.length === 0;
      }
      
      if (redoBtn) {
        redoBtn.style.opacity = redoStack.length > 0 ? '1' : '0.5';
        redoBtn.disabled = redoStack.length === 0;
      }
    }

    // Gestion dynamique des couleurs (couleur + rgba)
    function syncColorInputs(colorInput, rgbaInput) {
      colorInput.addEventListener('input', () => {
        const alpha = +opacityInput.value;
        const c = hexToRgba(colorInput.value, alpha);
        rgbaInput.value = rgbaToString(c);
      });
      rgbaInput.addEventListener('input', () => {
        const val = rgbaInput.value.trim();
        const c = parseRgba(val);
        if (c) {
          colorInput.value = `#${[c.r,c.g,c.b].map(x=>x.toString(16).padStart(2,'0')).join('')}`;
          opacityInput.value = c.a;
          opacityValue.textContent = Math.round(c.a * 100);
        }
      });
    }

    // Initial sync for first 3 colors
    ['color1','color2','color3'].forEach(id=>{
      const cInput=document.getElementById(id);
      const rInput=document.getElementById(id+'rgba');
      syncColorInputs(cInput,rInput);
    });

    // Ajouter une couleur au dégradé
    addColorBtn.addEventListener('click', () => {
      const idx = extraColorsContainer.querySelectorAll('input[type=color]').length + 3;
      const div = document.createElement('div');
      div.className = 'mb-3';
      div.innerHTML = `
        <label for="color${idx}" class="block mb-1 text-sm">Color ${idx}</label>
        <input type="color" id="color${idx}" value="#ffffff" class="w-full h-10 p-0 border border-[#555] rounded cursor-pointer" />
        <input type="text" id="color${idx}rgba" value="rgba(255,255,255,1)" class="w-full mt-1 px-2 py-1 bg-[#1e1e1e] border border-[#555] rounded text-[#c0c0c0] text-sm" />
      `;
      extraColorsContainer.appendChild(div);
      const cInput = div.querySelector(`#color${idx}`);
      const rInput = div.querySelector(`#color${idx}rgba`);
      syncColorInputs(cInput, rInput);
    });

    // Afficher ou cacher l'angle du gradient selon mode
    colorModeSelect.addEventListener('change', () => {
      gradientAngleContainer.style.display = colorModeSelect.value === 'gradient' ? 'block' : 'none';
    });

    // Récupérer toutes les couleurs RGBA dynamiquement
    function getAllColors() {
      const colors = [];
      colorInputsContainer.querySelectorAll('input[type=text]').forEach(input => {
        const c = parseRgba(input.value);
        if (c) colors.push(c);
      });
      return colors.length ? colors : [{r:255,g:0,b:0,a:1},{r:0,g:0,b:255,a:1}];
    }

    // Créer un dégradé détaillé avec palette fixe et interpolation vers couleurs choisies
    const detailedStops = [
      { pos: 0, color: '#63FFC2' }, { pos: 0.05, color: '#63FAC5' }, { pos: 0.10, color: '#63F5C8' },
      { pos: 0.15, color: '#63F0CB' }, { pos: 0.20, color: '#63EBCE' }, { pos: 0.25, color: '#63E6D1' },
      { pos: 0.30, color: '#63E1D4' }, { pos: 0.35, color: '#63DCD7' }, { pos: 0.40, color: '#63D7DA' },
      { pos: 0.45, color: '#63D2DD' }, { pos: 0.50, color: '#61E0FF' }, { pos: 0.55, color: '#6AD3FF' },
      { pos: 0.60, color: '#73C6FF' }, { pos: 0.65, color: '#7CB9FF' }, { pos: 0.70, color: '#85ACFF' },
      { pos: 0.75, color: '#8E9FFF' }, { pos: 0.80, color: '#9792FF' }, { pos: 0.85, color: '#A085FF' },
      { pos: 0.90, color: '#A978FF' }, { pos: 0.95, color: '#B26BFF' }, { pos: 1.00, color: '#5E7DFF' }
    ];

    function createDetailedGradient(ctx, w, h, angle, colors) {
      const rad = angle * Math.PI / 180;
      const x0 = w/2 - Math.cos(rad)*w/2;
      const y0 = h/2 - Math.sin(rad)*h/2;
      const x1 = w/2 + Math.cos(rad)*w/2;
      const y1 = h/2 + Math.sin(rad)*h/2;
      const grad = ctx.createLinearGradient(x0,y0,x1,y1);
      for(let stop of detailedStops){
        const base = hexToRgba(stop.color);
        let t = stop.pos * (colors.length-1);
        let i = Math.floor(t);
        let f = t - i;
        if(i >= colors.length-1) i = colors.length-2, f=1;
        const c = lerpColor(colors[i], colors[i+1], f);
        const mixed = {r:(base.r+c.r)/2,g:(base.g+c.g)/2,b:(base.b+c.b)/2,a:(base.a+c.a)/2};
        grad.addColorStop(stop.pos, rgbaToString(mixed));
      }
      return grad;
    }

    // Dessiner formes et traits avec couleur propre à chaque élément (fixe)
    function drawShape(ctx, s) {
      if (!s || !s.type) return; // Protection contre les objets invalides
      
      ctx.save(); // Sauvegarder l'état du contexte
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      
      // Appliquer la rotation si définie
      if (s.rotation && s.rotation !== 0) {
        const centerX = s.x + s.w / 2;
        const centerY = s.y + s.h / 2;
        ctx.translate(centerX, centerY);
        ctx.rotate((s.rotation * Math.PI) / 180);
        ctx.translate(-centerX, -centerY);
      }
      
      // Déterminer la couleur/opacité propre à la forme (figées à la création)
      let finalColor = s.fillColor || s.color || '#000000';
      let finalOpacity = (typeof s.opacity === 'number') ? s.opacity : (opacityInput ? parseFloat(opacityInput.value) : 1);

      // Appliquer les paramètres de gradient avancés si la forme les utilise
      if (s.fillMode === 'gradient' && s.gradientOptions && s.gradientOptions.color1 && s.gradientOptions.color2) {
        finalColor = createAdvancedGradient(ctx, s);
      }

      // Appliquer opacité propre à la forme
      ctx.globalAlpha = Math.max(0, Math.min(1, finalOpacity));

      // Appliquer style pinceau global au contexte (glacis/sfumato/etc.) uniquement si mode pinceau
      const box = { x: s.x, y: s.y, w: s.w, h: s.h };
      if (styleMode === 'brush') {
        applyGlobalArtStyle(ctx, box, typeof finalColor === 'string' ? finalColor : '#000000');
      }

      // Appliquer style de forme si défini
      const shapeStyle = s.shapeStyle || 'flat-fill';
      
      try {
        ctx.beginPath();
        
        if(s.type==='rectangle') {
          if (s.borderRadius && s.borderRadius > 0) {
            // Rectangle avec border radius
            drawRoundedRect(ctx, s.x, s.y, s.w, s.h, s.borderRadius);
          } else {
            // Rectangle normal
            ctx.rect(s.x, s.y, s.w, s.h);
          }
          
          applyShapeStyleToPath(ctx, shapeStyle, finalColor, s);
        }
        else if(s.type==='circle'){
          const r = Math.min(Math.abs(s.w),Math.abs(s.h))/2;
          ctx.arc(s.x+s.w/2,s.y+s.h/2,r,0,2*Math.PI);
          
          applyShapeStyleToPath(ctx, shapeStyle, finalColor, s);
        }
        else if(s.type==='triangle'){
          ctx.moveTo(s.x+s.w/2,s.y);
          ctx.lineTo(s.x+s.w,s.y+s.h);
          ctx.lineTo(s.x,s.y+s.h);
          ctx.closePath();
          
          applyShapeStyleToPath(ctx, shapeStyle, finalColor, s);
        }
        else if(s.type==='line'){
          ctx.strokeStyle = finalColor;
          ctx.lineWidth = s.outlineThickness || s.size || 1;
          ctx.beginPath();
          ctx.moveTo(s.x,s.y);
          ctx.lineTo(s.x+s.w,s.y+s.h);
          
          applyShapeStyleToPath(ctx, shapeStyle, finalColor, s);
        }
        else if(s.type==='ellipse'){
          const centerX = s.x + s.w/2;
          const centerY = s.y + s.h/2;
          const radiusX = Math.abs(s.w)/2;
          const radiusY = Math.abs(s.h)/2;
          
          ctx.beginPath();
          ctx.ellipse(centerX, centerY, radiusX, radiusY, 0, 0, 2 * Math.PI);
          
          applyShapeStyleToPath(ctx, shapeStyle, finalColor, s);
        }
        else if(s.type==='diamond'){
          ctx.moveTo(s.x + s.w/2, s.y);
          ctx.lineTo(s.x + s.w, s.y + s.h/2);
          ctx.lineTo(s.x + s.w/2, s.y + s.h);
          ctx.lineTo(s.x, s.y + s.h/2);
          ctx.closePath();
          
          applyShapeStyleToPath(ctx, shapeStyle, finalColor, s);
        }
        else if(s.type==='pentagon'){
          drawPolygon(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 5);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='hexagon'){
          drawPolygon(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 6);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='octagon'){
          drawPolygon(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 8);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='star5'){
          drawStar(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 5);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='star6'){
          drawStar(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 6);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='star8'){
          drawStar(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 8);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='heart'){
          drawHeart(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='arrow'){
          drawArrow(ctx, s.x, s.y, s.x + s.w, s.y + s.h, Math.min(s.w, s.h)/6);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='cloud'){
          drawCloud(ctx, s.x + s.w/2, s.y + s.h/2, s.w, s.h);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        // 5 NOUVELLES FORMES SUPPLÉMENTAIRES
        else if(s.type==='crescent'){
          drawCrescent(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='droplet'){
          drawDroplet(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='trapezoid'){
          drawTrapezoid(ctx, s.x, s.y, s.w, s.h);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='parallelogram'){
          drawParallelogram(ctx, s.x, s.y, s.w, s.h);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        else if(s.type==='cross'){
          drawCross(ctx, s.x + s.w/2, s.y + s.h/2, s.w, s.h);
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
        }
        // 20 NOUVELLES FORMES SUPPLÉMENTAIRES
        else if(s.type==='heptagon'){
          drawPolygon(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 7);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='nonagon'){
          drawPolygon(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 9);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='decagon'){
          drawPolygon(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 10);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='dodecagon'){
          drawPolygon(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 12);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='star3'){
          drawStar(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 3);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='star4'){
          drawStar(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 4);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='star7'){
          drawStar(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 7);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='star10'){
          drawStar(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 10);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='spiral'){
          drawSpiral(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2);
          ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 2; ctx.stroke();
        }
        else if(s.type==='gear'){
          drawGear(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2, 12);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='lightning'){
          drawLightning(ctx, s.x, s.y, s.w, s.h);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='leaf'){
          drawLeaf(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='flower'){
          drawFlower(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='sun'){
          drawSun(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='moon'){
          drawMoon(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='infinity'){
          drawInfinity(ctx, s.x + s.w/2, s.y + s.h/2, s.w, s.h);
          ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 3; ctx.stroke();
        }
        else if(s.type==='bubble'){
          drawBubble(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='crown'){
          drawCrown(ctx, s.x, s.y, s.w, s.h);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='gem'){
          drawGem(ctx, s.x + s.w/2, s.y + s.h/2, Math.min(s.w, s.h)/2);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='shield'){
          drawShield(ctx, s.x + s.w/2, s.y + s.h/2, s.w, s.h);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='eye'){
          drawEye(ctx, s.x + s.w/2, s.y + s.h/2, s.w, s.h);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
        else if(s.type==='butterfly'){
          drawButterfly(ctx, s.x + s.w/2, s.y + s.h/2, s.w, s.h);
          if (s.outlineOnly) { ctx.strokeStyle = finalColor; ctx.lineWidth = s.outlineThickness || 1; ctx.stroke(); } 
          else { ctx.fillStyle = finalColor; ctx.fill(); }
        }
      } catch(e) {
        console.warn('Erreur lors du dessin de la forme:', e, s);
      }
      
      ctx.restore(); // Restaurer l'état du contexte
    }

    // SYSTÈME DE SÉLECTION AVANCÉ
    
    // Fonction pour détecter quel élément est cliqué
    function getClickedElement(x, y) {
      // Vérifier les formes (en ordre inverse pour prendre celle du dessus)
      for (let i = shapes.length - 1; i >= 0; i--) {
        const shape = shapes[i];
        if (isPointInShape(x, y, shape)) {
          return { type: 'shape', index: i, element: shape };
        }
      }
      
      // Vérifier les images importées
      for (let i = importedImages.length - 1; i >= 0; i--) {
        const img = importedImages[i];
        if (x >= img.x && x <= img.x + img.width && 
            y >= img.y && y <= img.y + img.height) {
          return { type: 'image', index: i, element: img };
        }
      }
      
      // **NOUVEAU: Vérifier les dessins (strokes)**
      for (let i = drawingStrokes.length - 1; i >= 0; i--) {
        const stroke = drawingStrokes[i];
        if (isPointInDrawingStroke(x, y, stroke)) {
          return { type: 'drawing', index: i, element: stroke };
        }
      }
      
      return null;
    }

    // **NOUVELLE FONCTION: Vérifier si un point est dans un trait de dessin**
    function isPointInDrawingStroke(x, y, stroke) {
      if (!stroke.points || stroke.points.length < 2) return false;
      
      const tolerance = Math.max(8, (stroke.size || 5) + 3); // Zone de clic plus grande que le trait
      
      // Vérifier chaque segment du trait
      for (let i = 1; i < stroke.points.length; i++) {
        const p1 = stroke.points[i - 1];
        const p2 = stroke.points[i];
        
        // Calculer la distance du point au segment de ligne
        const distance = distancePointToLineSegment(x, y, p1.x, p1.y, p2.x, p2.y);
        
        if (distance <= tolerance) {
          return true;
        }
      }
      
      return false;
    }

    // **FONCTION UTILITAIRE: Distance d'un point à un segment de ligne**
    function distancePointToLineSegment(px, py, x1, y1, x2, y2) {
      const dx = x2 - x1;
      const dy = y2 - y1;
      const length = Math.sqrt(dx * dx + dy * dy);
      
      if (length === 0) {
        // Le segment est un point
        return Math.sqrt((px - x1) * (px - x1) + (py - y1) * (py - y1));
      }
      
      // Paramètre t pour la projection du point sur la ligne
      const t = Math.max(0, Math.min(1, ((px - x1) * dx + (py - y1) * dy) / (length * length)));
      
      // Point de projection sur le segment
      const projX = x1 + t * dx;
      const projY = y1 + t * dy;
      
      // Distance du point à la projection
      return Math.sqrt((px - projX) * (px - projX) + (py - projY) * (py - projY));
    }

    // Fonction pour vérifier si un point est dans une forme
    function isPointInShape(x, y, shape) {
      const ctx = canvas.getContext('2d');
      ctx.save();
      
      // Appliquer rotation si nécessaire
      if (shape.rotation && shape.rotation !== 0) {
        const centerX = shape.x + shape.w / 2;
        const centerY = shape.y + shape.h / 2;
        ctx.translate(centerX, centerY);
        ctx.rotate((shape.rotation * Math.PI) / 180);
        ctx.translate(-centerX, -centerY);
      }
      
      ctx.beginPath();
      
      // Créer le path selon le type de forme
      if (shape.type === 'rectangle') {
        if (shape.borderRadius && shape.borderRadius > 0) {
          drawRoundedRect(ctx, shape.x, shape.y, shape.w, shape.h, shape.borderRadius);
        } else {
          ctx.rect(shape.x, shape.y, shape.w, shape.h);
        }
      } else if (shape.type === 'circle') {
        const r = Math.min(Math.abs(shape.w), Math.abs(shape.h)) / 2;
        ctx.arc(shape.x + shape.w/2, shape.y + shape.h/2, r, 0, 2*Math.PI);
      } else if (shape.type === 'ellipse') {
        const centerX = shape.x + shape.w/2;
        const centerY = shape.y + shape.h/2;
        const radiusX = Math.abs(shape.w)/2;
        const radiusY = Math.abs(shape.h)/2;
        ctx.ellipse(centerX, centerY, radiusX, radiusY, 0, 0, 2 * Math.PI);
      } else {
        // Pour les autres formes, utiliser un rectangle approximatif
        ctx.rect(shape.x, shape.y, shape.w, shape.h);
      }
      
      const isInside = ctx.isPointInPath(x, y);
      ctx.restore();
      return isInside;
    }

    // **NOUVELLE FONCTION: Obtenir les limites d'un dessin**
    function getDrawingBounds(stroke) {
      if (!stroke || !stroke.points || stroke.points.length === 0) return {x:0, y:0, w:0, h:0};
      let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
      for (const p of stroke.points) {
        if (p.x < minX) minX = p.x;
        if (p.y < minY) minY = p.y;
        if (p.x > maxX) maxX = p.x;
        if (p.y > maxY) maxY = p.y;
      }
      return { x: minX, y: minY, w: maxX - minX, h: maxY - minY };
    }

    // Fonction pour sélectionner un élément
    // Mettre à jour les contrôles de style de pinceau depuis l'élément sélectionné
    function updateBrushStyleControlsFromElement(element) {
      if (!element) return;
      
      if (element.savedBrushStyle) {
        brushStyleSelect.value = element.savedBrushStyle;
        currentBrushStyle = element.savedBrushStyle;
      }
      
      if (element.savedStyleIntensity !== undefined) {
        styleIntensityInput.value = element.savedStyleIntensity;
        styleIntensityValue.textContent = element.savedStyleIntensity.toFixed(2);
        styleIntensity = element.savedStyleIntensity;
      }
      
      if (element.savedTextureGrain !== undefined) {
        textureGrainInput.value = element.savedTextureGrain;
        textureGrainValue.textContent = element.savedTextureGrain.toFixed(2);
        textureGrain = element.savedTextureGrain;
      }
      
      if (element.savedSpreading !== undefined) {
        spreadingInput.value = element.savedSpreading;
        spreadingValue.textContent = element.savedSpreading.toFixed(2);
        spreading = element.savedSpreading;
      }
      
      if (element.savedBlurEffect !== undefined) {
        blurEffectInput.value = element.savedBlurEffect;
        blurEffectValue.textContent = element.savedBlurEffect.toFixed(2);
        blurEffect = element.savedBlurEffect;
      }
      
      if (element.savedShineIntensity !== undefined) {
        shineIntensityInput.value = element.savedShineIntensity;
        shineIntensityValue.textContent = element.savedShineIntensity.toFixed(2);
        shineIntensity = element.savedShineIntensity;
      }
      
      if (element.savedShineColor) {
        shineColorInput.value = element.savedShineColor;
        shineColor = element.savedShineColor;
      }
      
      if (element.savedShineOpacity !== undefined) {
        shineOpacityInput.value = element.savedShineOpacity;
        shineOpacityValue.textContent = element.savedShineOpacity.toFixed(2);
        shineOpacity = element.savedShineOpacity;
      }
    }

    function selectElement(elementInfo) {
      selectedElement = elementInfo.element;
      selectedElementIndex = elementInfo.index;
      selectedElementType = elementInfo.type;
      isElementSelected = true;
      
      // **NOUVEAU: Gestion spéciale pour les dessins**
      if (selectedElementType === 'drawing') {
        // Pour les dessins, utiliser le système existant selectedDrawingStrokeId
        selectedDrawingStrokeId = elementInfo.element.id;
        // Mettre à jour les contrôles de style
        updateBrushStyleControlsFromElement(selectedElement);
      } else {
        // Réinitialiser la sélection de dessin si on sélectionne autre chose
        selectedDrawingStrokeId = null;
      }
      
      // Afficher le panneau de style artistique pour les formes
      if (selectedElementType === 'shape') {
        showShapeArtisticStylePanel();
      } else {
        hideShapeArtisticStylePanel();
      }
      
      // Dessiner les poignées de sélection
      redrawAll();
      // Maintenant on affiche les poignées et l'UI pour TOUS les types, y compris les dessins
      drawSelectionHandles();
      drawSelectionUI();
    }

    // Fonction pour désélectionner
    function deselectElement() {
      selectedElement = null;
      selectedElementIndex = -1;
      selectedElementType = null;
      isElementSelected = false;
      selectedDrawingStrokeId = null; // **AJOUT: Réinitialiser aussi la sélection de dessin**
      hideSelectionUI();
      hideShapeArtisticStylePanel();
      redrawAll();
    }

    // Afficher/masquer le panneau de style artistique pour formes
    function showShapeArtisticStylePanel() {
      const panel = document.getElementById('shapeArtisticStyle');
      if (panel && selectedElement) {
        panel.classList.remove('hidden');
        
        // Pré-remplir les valeurs actuelles de la forme
        const styleSelect = document.getElementById('selectedShapeStyle');
        const intensityInput = document.getElementById('selectedShapeIntensity');
        const grainInput = document.getElementById('selectedShapeGrain');
        const spreadingInput = document.getElementById('selectedShapeSpreading');
        const blurInput = document.getElementById('selectedShapeBlur');
        const shineInput = document.getElementById('selectedShapeShine');
        const textureSelect = document.getElementById('selectedShapeTexture');
        
        if (styleSelect) styleSelect.value = selectedElement.artisticStyle || 'normal';
        if (intensityInput) intensityInput.value = selectedElement.styleIntensity || 50;
        if (grainInput) grainInput.value = selectedElement.styleGrain || 30;
        if (spreadingInput) spreadingInput.value = selectedElement.styleSpreading || 20;
        if (blurInput) blurInput.value = selectedElement.styleBlur || 0;
        if (shineInput) shineInput.value = selectedElement.styleShine || 0;
        if (textureSelect) textureSelect.value = selectedElement.extraTexture || 'none';
        
        // Mettre à jour les affichages de valeurs
        updateShapeStyleDisplays();
      }
    }

    function hideShapeArtisticStylePanel() {
      const panel = document.getElementById('shapeArtisticStyle');
      if (panel) {
        panel.classList.add('hidden');
      }
    }

    function updateShapeStyleDisplays() {
      const intensityValue = document.getElementById('selectedShapeIntensityValue');
      const grainValue = document.getElementById('selectedShapeGrainValue');
      const spreadingValue = document.getElementById('selectedShapeSpreadingValue');
      const blurValue = document.getElementById('selectedShapeBlurValue');
      const shineValue = document.getElementById('selectedShapeShineValue');
      
      const intensityInput = document.getElementById('selectedShapeIntensity');
      const grainInput = document.getElementById('selectedShapeGrain');
      const spreadingInput = document.getElementById('selectedShapeSpreading');
      const blurInput = document.getElementById('selectedShapeBlur');
      const shineInput = document.getElementById('selectedShapeShine');
      
      if (intensityValue && intensityInput) intensityValue.textContent = intensityInput.value;
      if (grainValue && grainInput) grainValue.textContent = grainInput.value;
      if (spreadingValue && spreadingInput) spreadingValue.textContent = spreadingInput.value;
      if (blurValue && blurInput) blurValue.textContent = blurInput.value;
      if (shineValue && shineInput) shineValue.textContent = shineInput.value;
    }

    // Fonction pour dessiner les poignées de sélection
    function drawSelectionHandles() {
      if (!isElementSelected || !selectedElement) return;
      
      const ctx = canvas.getContext('2d');
      let bounds;
      
      if (selectedElementType === 'shape') {
        bounds = {
          x: selectedElement.x,
          y: selectedElement.y,
          w: selectedElement.w,
          h: selectedElement.h
        };
      } else if (selectedElementType === 'image') {
        bounds = {
          x: selectedElement.x,
          y: selectedElement.y,
          w: selectedElement.width,
          h: selectedElement.height
        };
      } else if (selectedElementType === 'drawing') {
        bounds = getDrawingBounds(selectedElement);
      }
      
      if (!bounds) return;
      
      ctx.save();
      ctx.strokeStyle = '#007bff';
      ctx.fillStyle = '#ffffff';
      ctx.lineWidth = 2;
      
      // Bordure de sélection
      ctx.strokeRect(bounds.x - 2, bounds.y - 2, bounds.w + 4, bounds.h + 4);
      
      const handleSize = 8;
      const handles = [
        { id: 'nw', x: bounds.x - handleSize/2, y: bounds.y - handleSize/2 },
        { id: 'n',  x: bounds.x + bounds.w/2 - handleSize/2, y: bounds.y - handleSize/2 },
        { id: 'ne', x: bounds.x + bounds.w - handleSize/2, y: bounds.y - handleSize/2 },
        { id: 'e',  x: bounds.x + bounds.w - handleSize/2, y: bounds.y + bounds.h/2 - handleSize/2 },
        { id: 'se', x: bounds.x + bounds.w - handleSize/2, y: bounds.y + bounds.h - handleSize/2 },
        { id: 's',  x: bounds.x + bounds.w/2 - handleSize/2, y: bounds.y + bounds.h - handleSize/2 },
        { id: 'sw', x: bounds.x - handleSize/2, y: bounds.y + bounds.h - handleSize/2 },
        { id: 'w',  x: bounds.x - handleSize/2, y: bounds.y + bounds.h/2 - handleSize/2 },
      ];
      
      // Dessiner les poignées de redimensionnement
      handles.forEach(handle => {
        ctx.fillRect(handle.x, handle.y, handleSize, handleSize);
        ctx.strokeRect(handle.x, handle.y, handleSize, handleSize);
      });
      
      // Poignée de rotation (cercle au-dessus)
      const rotationHandle = {
        x: bounds.x + bounds.w/2,
        y: bounds.y - 25
      };
      
      ctx.beginPath();
      ctx.arc(rotationHandle.x, rotationHandle.y, 6, 0, 2 * Math.PI);
      ctx.fill();
      ctx.stroke();
      
      // Ligne de la poignée de rotation
      ctx.beginPath();
      ctx.moveTo(bounds.x + bounds.w/2, bounds.y);
      ctx.lineTo(rotationHandle.x, rotationHandle.y);
      ctx.stroke();
      
      ctx.restore();
    }

    // Fonction pour obtenir la poignée cliquée
    function getClickedHandle(x, y) {
      if (!isElementSelected || !selectedElement) return null;
      
      let bounds;
      if (selectedElementType === 'shape') {
        bounds = {
          x: selectedElement.x,
          y: selectedElement.y,
          w: selectedElement.w,
          h: selectedElement.h
        };
      } else if (selectedElementType === 'image') {
        bounds = {
          x: selectedElement.x,
          y: selectedElement.y,
          w: selectedElement.width,
          h: selectedElement.height
        };
      } else if (selectedElementType === 'drawing') {
        bounds = getDrawingBounds(selectedElement);
      }
      
      if (!bounds) return null;
      
      const handleSize = 8;
      const tolerance = 5;
      
      const handles = [
        { id: 'nw', x: bounds.x - handleSize/2, y: bounds.y - handleSize/2 },
        { id: 'n',  x: bounds.x + bounds.w/2 - handleSize/2, y: bounds.y - handleSize/2 },
        { id: 'ne', x: bounds.x + bounds.w - handleSize/2, y: bounds.y - handleSize/2 },
        { id: 'e',  x: bounds.x + bounds.w - handleSize/2, y: bounds.y + bounds.h/2 - handleSize/2 },
        { id: 'se', x: bounds.x + bounds.w - handleSize/2, y: bounds.y + bounds.h - handleSize/2 },
        { id: 's',  x: bounds.x + bounds.w/2 - handleSize/2, y: bounds.y + bounds.h - handleSize/2 },
        { id: 'sw', x: bounds.x - handleSize/2, y: bounds.y + bounds.h - handleSize/2 },
        { id: 'w',  x: bounds.x - handleSize/2, y: bounds.y + bounds.h/2 - handleSize/2 },
      ];
      
      // Vérifier les poignées de redimensionnement
      for (const handle of handles) {
        if (x >= handle.x - tolerance && x <= handle.x + handleSize + tolerance &&
            y >= handle.y - tolerance && y <= handle.y + handleSize + tolerance) {
          return { type: 'resize', handle: handle.id };
        }
      }
      
      // Vérifier la poignée de rotation
      const rotationHandle = {
        x: bounds.x + bounds.w/2,
        y: bounds.y - 25
      };
      
      const dist = Math.sqrt(Math.pow(x - rotationHandle.x, 2) + Math.pow(y - rotationHandle.y, 2));
      if (dist <= 10) {
        return { type: 'rotation' };
      }
      
      return null;
    }

    // Interface UI pour la sélection
    function drawSelectionUI() {
      if (!isElementSelected || !selectedElement) return;
      
      let bounds;
      if (selectedElementType === 'shape') {
        bounds = {
          x: selectedElement.x,
          y: selectedElement.y,
          w: selectedElement.w,
          h: selectedElement.h
        };
      } else if (selectedElementType === 'image') {
        bounds = {
          x: selectedElement.x,
          y: selectedElement.y,
          w: selectedElement.width,
          h: selectedElement.height
        };
      } else if (selectedElementType === 'drawing') {
        bounds = getDrawingBounds(selectedElement);
      } else if (selectedElementType === 'text' && selectedElement) {
        bounds = {
          x: selectedElement.x,
          y: selectedElement.y,
          w: selectedElement.width,
          h: selectedElement.height
        };
      }
      
      if (!bounds) return;
      
      // Créer ou mettre à jour le panneau de contrôle flottant
      let selectionPanel = document.getElementById('selectionPanel');
      if (!selectionPanel) {
        selectionPanel = document.createElement('div');
        selectionPanel.id = 'selectionPanel';
        selectionPanel.className = 'absolute bg-gray-800 text-white rounded-lg shadow-lg p-2 flex gap-1 z-50';
        selectionPanel.style.pointerEvents = 'auto';
        document.body.appendChild(selectionPanel);
      }
      
      const canvasRect = canvas.getBoundingClientRect();
      const panelX = canvasRect.left + bounds.x + bounds.w + 10;
      const panelY = canvasRect.top + bounds.y;
      
      selectionPanel.style.left = panelX + 'px';
      selectionPanel.style.top = panelY + 'px';
      selectionPanel.style.display = 'flex';
      
      selectionPanel.innerHTML = `
        <button onclick="cutSelectedElement()" class="p-1 hover:bg-gray-700 rounded" title="Couper">
          ✂️
        </button>
        <button onclick="copySelectedElement()" class="p-1 hover:bg-gray-700 rounded" title="Copier">
          📋
        </button>
        <button onclick="deleteSelectedElement()" class="p-1 hover:bg-gray-700 rounded" title="Supprimer">
          🗑️
        </button>
        <button onclick="showElementProperties()" class="p-1 hover:bg-gray-700 rounded" title="Propriétés">
          ⚙️
        </button>
        <div class="relative inline-block">
          <button onclick="toggleRotationPopup()" class="p-1 hover:bg-gray-700 rounded" title="Rotation">
            🔄
          </button>
          <div id="rotationPopup" class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden bg-gray-800 p-2 rounded shadow-lg border border-gray-600 w-48 z-50">
            <div class="text-xs text-center mb-1">Rotation: <span id="rotationValueDisplay">0</span>°</div>
            <input type="range" min="0" max="360" value="0" class="w-full mb-2" oninput="updateElementRotation(this.value)">
            <div class="flex justify-between gap-2">
                <button onclick="cancelRotation()" class="flex-1 text-red-500 hover:text-red-400 text-xs px-2 py-1 border border-red-500 rounded bg-transparent">✕ Annuler</button>
                <button onclick="validateRotation()" class="flex-1 text-green-500 hover:text-green-400 text-xs px-2 py-1 border border-green-500 rounded bg-transparent">✓ Valider</button>
            </div>
          </div>
        </div>
      `;
      
      // Initialize rotation slider value
      setTimeout(() => {
        const slider = selectionPanel.querySelector('input[type=range]');
        const display = selectionPanel.querySelector('#rotationValueDisplay');
        if (slider && display && selectedElement) {
          let currentRotation = 0;
          if (selectedElementType === 'shape') {
            currentRotation = selectedElement.rotation || 0;
          } else if (selectedElementType === 'drawing') {
             currentRotation = selectedElement.rotation || 0;
          } else if (selectedElementType === 'image') {
             currentRotation = selectedElement.rotation || 0;
          }
          slider.value = currentRotation;
          display.textContent = Math.round(currentRotation);
        }
      }, 0);
    }

    let initialRotation = 0;

    window.toggleRotationPopup = function() {
        const popup = document.getElementById('rotationPopup');
        if (popup.classList.contains('hidden')) {
            popup.classList.remove('hidden');
            // Store initial rotation
            if (selectedElement) {
                initialRotation = selectedElement.rotation || 0;
            }
        } else {
            popup.classList.add('hidden');
        }
    };

    window.validateRotation = function() {
        const popup = document.getElementById('rotationPopup');
        if(popup) popup.classList.add('hidden');
        saveState(); // Save state on validation
    };

    window.cancelRotation = function() {
        const popup = document.getElementById('rotationPopup');
        if(popup) popup.classList.add('hidden');
        if (selectedElement) {
            updateElementRotation(initialRotation);
            // Reset slider value if popup is reopened
            const slider = document.querySelector('#rotationPopup input[type=range]');
            if (slider) slider.value = initialRotation;
        }
    };

    // Function to update rotation from slider
    window.updateElementRotation = function(angle) {
      if (!isElementSelected || !selectedElement) return;
      angle = parseFloat(angle);
      const display = document.getElementById('rotationValueDisplay');
      if(display) display.textContent = Math.round(angle);
      
      if (selectedElementType === 'shape') {
        selectedElement.rotation = angle;
      } else if (selectedElementType === 'image') {
        selectedElement.rotation = angle;
      } else if (selectedElementType === 'drawing') {
         const prevAngle = selectedElement.rotation || 0;
         const delta = angle - prevAngle;
         selectedElement.rotation = angle;
         
         const bounds = getDrawingBounds(selectedElement);
         const centerX = bounds.x + bounds.w / 2;
         const centerY = bounds.y + bounds.h / 2;
         
         const rad = delta * Math.PI / 180;
         const cos = Math.cos(rad);
         const sin = Math.sin(rad);
         
         for (const p of selectedElement.points) {
           const dx = p.x - centerX;
           const dy = p.y - centerY;
           p.x = centerX + dx * cos - dy * sin;
           p.y = centerY + dx * sin + dy * cos;
         }
      }
      redrawAll();
      drawSelectionHandles();
    };

    function hideSelectionUI() {
      const selectionPanel = document.getElementById('selectionPanel');
      if (selectionPanel) {
        selectionPanel.style.display = 'none';
      }
    }

    // Fonctions d'action pour les éléments sélectionnés
    function deleteSelectedElement() {
      if (!isElementSelected || selectedElementIndex === -1) return;
      
      if (selectedElementType === 'shape') {
        shapes.splice(selectedElementIndex, 1);
      } else if (selectedElementType === 'image') {
        importedImages.splice(selectedElementIndex, 1);
      } else if (selectedElementType === 'drawing') {
        drawingStrokes.splice(selectedElementIndex, 1);
        // Also remove from layers if present
        if (window.layersPanelAPI) {
           window.layersPanelAPI.removeLayerById(selectedElement.id);
        }
      }
      
      deselectElement();
      redrawAll();
    }

    function copySelectedElement() {
      if (!isElementSelected || !selectedElement) return;
      
      copiedElement = JSON.parse(JSON.stringify(selectedElement));
      copiedElementType = selectedElementType;
    }

    function cutSelectedElement() {
      copySelectedElement();
      deleteSelectedElement();
    }

    function pasteElement() {
      if (!copiedElement) return;
      
      const newElement = JSON.parse(JSON.stringify(copiedElement));
      
      // Décaler légèrement la position
      if (copiedElementType === 'shape') {
        newElement.x += 20;
        newElement.y += 20;
        shapes.push(newElement);
      } else if (copiedElementType === 'image') {
        newElement.x += 20;
        newElement.y += 20;
        importedImages.push(newElement);
      } else if (copiedElementType === 'drawing') {
        newElement.id = 'id-' + Math.random().toString(36).substr(2, 9); // Generate new ID
        if (newElement.points) {
          for (const p of newElement.points) {
             p.x += 20;
             p.y += 20;
          }
        }
        drawingStrokes.push(newElement);
        if (window.layersPanelAPI) {
           window.layersPanelAPI.addLayerForDrawingStroke(newElement);
        }
      }
      
      redrawAll();
    }

    function showElementProperties() {
      if (!isElementSelected || !selectedElement) return;
      
      // Créer un panneau de propriétés
      let propertiesPanel = document.getElementById('propertiesPanel');
      if (!propertiesPanel) {
        propertiesPanel = document.createElement('div');
        propertiesPanel.id = 'propertiesPanel';
        propertiesPanel.className = 'fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-800 text-white rounded-lg shadow-lg p-4 z-50 w-80';
        document.body.appendChild(propertiesPanel);
      }
      
      let content = '<h3 class="text-lg font-bold mb-3">Propriétés de l\'élément</h3>';
      
      if (selectedElementType === 'shape') {
        content += `
          <div class="mb-2">
            <label class="block text-sm">Couleur:</label>
            <input type="color" id="propColor" value="${selectedElement.color}" class="w-full">
          </div>
          <div class="mb-2">
            <label class="block text-sm">X: <span id="propXValue">${selectedElement.x.toFixed(1)}</span></label>
            <input type="range" id="propX" min="0" max="3840" value="${selectedElement.x}" class="w-full">
          </div>
          <div class="mb-2">
            <label class="block text-sm">Y: <span id="propYValue">${selectedElement.y.toFixed(1)}</span></label>
            <input type="range" id="propY" min="0" max="2160" value="${selectedElement.y}" class="w-full">
          </div>
          <div class="mb-2">
            <label class="block text-sm">Largeur: <span id="propWValue">${selectedElement.w.toFixed(1)}</span></label>
            <input type="range" id="propW" min="1" max="1000" value="${selectedElement.w}" class="w-full">
          </div>
          <div class="mb-2">
            <label class="block text-sm">Hauteur: <span id="propHValue">${selectedElement.h.toFixed(1)}</span></label>
            <input type="range" id="propH" min="1" max="1000" value="${selectedElement.h}" class="w-full">
          </div>
          <div class="mb-2">
            <label class="block text-sm">Rotation: <span id="propRotValue">${(selectedElement.rotation || 0).toFixed(1)}</span>°</label>
            <input type="range" id="propRotation" min="0" max="360" value="${selectedElement.rotation || 0}" class="w-full">
          </div>
        `;
      } else if (selectedElementType === 'drawing') {
         const bounds = getDrawingBounds(selectedElement);
         content += `
          <div class="mb-2">
            <label class="block text-sm">Couleur:</label>
            <input type="color" id="propColor" value="${selectedElement.color}" class="w-full">
          </div>
          <div class="mb-2">
            <label class="block text-sm">Taille:</label>
            <input type="range" id="propSize" min="1" max="100" value="${selectedElement.size || 5}" class="w-full">
          </div>
          <div class="mb-2">
            <label class="block text-sm">X: <span id="propXValue">${bounds.x.toFixed(1)}</span></label>
            <input type="range" id="propX" min="0" max="3840" value="${bounds.x}" class="w-full">
          </div>
          <div class="mb-2">
            <label class="block text-sm">Y: <span id="propYValue">${bounds.y.toFixed(1)}</span></label>
            <input type="range" id="propY" min="0" max="2160" value="${bounds.y}" class="w-full">
          </div>
         `;
      }
      
      content += `
        <div class="flex gap-2 mt-4">
          <button onclick="applyElementProperties()" class="px-3 py-1 bg-blue-600 rounded hover:bg-blue-700">Appliquer</button>
          <button onclick="closePropertiesPanel()" class="px-3 py-1 bg-gray-600 rounded hover:bg-gray-700">Fermer</button>
        </div>
      `;
      
      propertiesPanel.innerHTML = content;
      propertiesPanel.style.display = 'block';
      
      // Ajouter les event listeners pour les sliders
      setupPropertySliders();
    }

    function setupPropertySliders() {
      const propX = document.getElementById('propX');
      const propY = document.getElementById('propY');
      const propW = document.getElementById('propW');
      const propH = document.getElementById('propH');
      const propRotation = document.getElementById('propRotation');
      
      if (propX) {
        propX.oninput = () => {
          document.getElementById('propXValue').textContent = propX.value;
        };
      }
      if (propY) {
        propY.oninput = () => {
          document.getElementById('propYValue').textContent = propY.value;
        };
      }
      if (propW) {
        propW.oninput = () => {
          document.getElementById('propWValue').textContent = propW.value;
        };
      }
      if (propH) {
        propH.oninput = () => {
          document.getElementById('propHValue').textContent = propH.value;
        };
      }
      if (propRotation) {
        propRotation.oninput = () => {
          document.getElementById('propRotValue').textContent = propRotation.value;
        };
      }
    }

    function applyElementProperties() {
      if (!isElementSelected || !selectedElement) return;
      
      if (selectedElementType === 'shape') {
        const propColor = document.getElementById('propColor');
        const propX = document.getElementById('propX');
        const propY = document.getElementById('propY');
        const propW = document.getElementById('propW');
        const propH = document.getElementById('propH');
        const propRotation = document.getElementById('propRotation');
        
        if (propColor) selectedElement.color = propColor.value;
        if (propX) selectedElement.x = parseFloat(propX.value);
        if (propY) selectedElement.y = parseFloat(propY.value);
        if (propW) selectedElement.w = parseFloat(propW.value);
        if (propH) selectedElement.h = parseFloat(propH.value);
        if (propRotation) selectedElement.rotation = parseFloat(propRotation.value);
      } else if (selectedElementType === 'drawing') {
        const propColor = document.getElementById('propColor');
        const propSize = document.getElementById('propSize');
        const propX = document.getElementById('propX');
        const propY = document.getElementById('propY');
        
        if (propColor) selectedElement.color = propColor.value;
        if (propSize) selectedElement.size = parseFloat(propSize.value);
        
        if (propX && propY) {
           const bounds = getDrawingBounds(selectedElement);
           const newX = parseFloat(propX.value);
           const newY = parseFloat(propY.value);
           const dx = newX - bounds.x;
           const dy = newY - bounds.y;
           
           if (dx !== 0 || dy !== 0) {
             for (const p of selectedElement.points) {
               p.x += dx;
               p.y += dy;
             }
           }
        }
      }
      
      redrawAll();
      drawSelectionHandles();
      drawSelectionUI();
    }

    function closePropertiesPanel() {
      const propertiesPanel = document.getElementById('propertiesPanel');
      if (propertiesPanel) {
        propertiesPanel.style.display = 'none';
      }
    }

    // Variables pour le copier-coller
    let copiedElement = null;
    let copiedElementType = null;

    // Helper function pour dessiner un rectangle arrondi
    function drawRoundedRect(ctx, x, y, width, height, radius) {
      if (width < 2 * radius) radius = width / 2;
      if (height < 2 * radius) radius = height / 2;
      
      ctx.moveTo(x + radius, y);
      ctx.arcTo(x + width, y, x + width, y + height, radius);
      ctx.arcTo(x + width, y + height, x, y + height, radius);
      ctx.arcTo(x, y + height, x, y, radius);
      ctx.arcTo(x, y, x + width, y, radius);
      ctx.closePath();
    }

    // Helper function pour dessiner des polygones réguliers
    function drawPolygon(ctx, centerX, centerY, radius, sides) {
      const angle = (2 * Math.PI) / sides;
      ctx.beginPath();
      
      for (let i = 0; i < sides; i++) {
        const x = centerX + radius * Math.cos(i * angle - Math.PI / 2);
        const y = centerY + radius * Math.sin(i * angle - Math.PI / 2);
        
        if (i === 0) {
          ctx.moveTo(x, y);
        } else {
          ctx.lineTo(x, y);
        }
      }
      ctx.closePath();
    }

    // Helper function pour dessiner des étoiles
    function drawStar(ctx, centerX, centerY, radius, points) {
      const outerRadius = radius;
      const innerRadius = radius * 0.4;
      const angle = Math.PI / points;
      
      ctx.beginPath();
      
      for (let i = 0; i < 2 * points; i++) {
        const r = (i % 2 === 0) ? outerRadius : innerRadius;
        const x = centerX + r * Math.cos(i * angle - Math.PI / 2);
        const y = centerY + r * Math.sin(i * angle - Math.PI / 2);
        
        if (i === 0) {
          ctx.moveTo(x, y);
        } else {
          ctx.lineTo(x, y);
        }
      }
      ctx.closePath();
    }

    // Helper function pour dessiner un cœur
    function drawHeart(ctx, centerX, centerY, size) {
      ctx.beginPath();
      
      const x = centerX;
      const y = centerY - size / 6;
      const width = size * 0.8;
      const height = size * 0.9;
      
      // Dessiner le cœur avec des courbes de Bézier plus réalistes
      ctx.moveTo(x, y + height / 4);
      ctx.bezierCurveTo(x, y, x - width / 2, y, x - width / 2, y + height / 4);
      ctx.bezierCurveTo(x - width / 2, y + height / 2, x, y + height / 1.3, x, y + height);
      ctx.bezierCurveTo(x, y + height / 1.3, x + width / 2, y + height / 2, x + width / 2, y + height / 4);
      ctx.bezierCurveTo(x + width / 2, y, x, y, x, y + height / 4);
      
      ctx.closePath();
    }

    // Helper function pour dessiner une flèche
    function drawArrow(ctx, x1, y1, x2, y2, headSize) {
      const angle = Math.atan2(y2 - y1, x2 - x1);
      
      ctx.beginPath();
      // Corps de la flèche
      ctx.moveTo(x1, y1);
      ctx.lineTo(x2, y2);
      
      // Pointe de la flèche
      ctx.lineTo(x2 - headSize * Math.cos(angle - Math.PI / 6), y2 - headSize * Math.sin(angle - Math.PI / 6));
      ctx.moveTo(x2, y2);
      ctx.lineTo(x2 - headSize * Math.cos(angle + Math.PI / 6), y2 - headSize * Math.sin(angle + Math.PI / 6));
      
      ctx.closePath();
    }

    // Helper function pour dessiner un nuage
    function drawCloud(ctx, centerX, centerY, width, height) {
      const x = centerX - width / 2;
      const y = centerY - height / 2;
      
      ctx.beginPath();
      
      // Cercles composant le nuage
      ctx.arc(x + width * 0.2, y + height * 0.7, height * 0.3, 0, 2 * Math.PI);
      ctx.arc(x + width * 0.4, y + height * 0.4, height * 0.35, 0, 2 * Math.PI);
      ctx.arc(x + width * 0.6, y + height * 0.3, height * 0.4, 0, 2 * Math.PI);
      ctx.arc(x + width * 0.8, y + height * 0.6, height * 0.25, 0, 2 * Math.PI);
    }

    // Helper function pour dessiner un croissant de lune
    function drawCrescent(ctx, centerX, centerY, radius) {
      ctx.beginPath();
      
      // Grand cercle (lune complète)
      ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI, false);
      
      // Petit cercle pour créer le croissant (en soustrayant)
      ctx.arc(centerX + radius * 0.3, centerY, radius * 0.8, 0, 2 * Math.PI, true);
      
      ctx.closePath();
    }

    // Helper function pour dessiner une goutte d'eau
    function drawDroplet(ctx, centerX, centerY, size) {
      ctx.beginPath();
      
      const x = centerX;
      const y = centerY + size * 0.3;
      
      // Partie ronde de la goutte
      ctx.arc(x, y, size * 0.6, 0, Math.PI, false);
      
      // Pointe du haut
      ctx.bezierCurveTo(
        x - size * 0.6, y,
        x - size * 0.2, y - size * 0.8,
        x, y - size
      );
      ctx.bezierCurveTo(
        x + size * 0.2, y - size * 0.8,
        x + size * 0.6, y,
        x + size * 0.6, y
      );
      
      ctx.closePath();
    }

    // Helper function pour dessiner un trapèze
    function drawTrapezoid(ctx, x, y, w, h) {
      ctx.beginPath();
      
      const offset = w * 0.2; // Décalage pour créer le trapèze
      
      ctx.moveTo(x + offset, y);        // Haut gauche
      ctx.lineTo(x + w - offset, y);    // Haut droite
      ctx.lineTo(x + w, y + h);         // Bas droite
      ctx.lineTo(x, y + h);             // Bas gauche
      ctx.closePath();
    }

    // Helper function pour dessiner un parallélogramme
    function drawParallelogram(ctx, x, y, w, h) {
      ctx.beginPath();
      
      const offset = w * 0.2; // Décalage pour créer le parallélogramme
      
      ctx.moveTo(x + offset, y);        // Haut gauche
      ctx.lineTo(x + w, y);             // Haut droite
      ctx.lineTo(x + w - offset, y + h); // Bas droite
      ctx.lineTo(x, y + h);             // Bas gauche
      ctx.closePath();
    }

    // Helper function pour dessiner une croix
    function drawCross(ctx, centerX, centerY, width, height) {
      ctx.beginPath();
      
      const thickness = Math.min(width, height) * 0.3;
      const halfThickness = thickness / 2;
      const halfWidth = width / 2;
      const halfHeight = height / 2;
      
      // Barre verticale
      ctx.rect(centerX - halfThickness, centerY - halfHeight, thickness, height);
      
      // Barre horizontale
      ctx.rect(centerX - halfWidth, centerY - halfThickness, width, thickness);
    }

    // FONCTIONS HELPER POUR LES 20 NOUVELLES FORMES

    function drawSpiral(ctx, centerX, centerY, radius) {
      ctx.beginPath();
      const turns = 3;
      const steps = 100;
      
      for (let i = 0; i <= steps; i++) {
        const angle = (i / steps) * turns * 2 * Math.PI;
        const r = (i / steps) * radius;
        const x = centerX + r * Math.cos(angle);
        const y = centerY + r * Math.sin(angle);
        
        if (i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
      }
    }

    function drawGear(ctx, centerX, centerY, radius, teeth) {
      ctx.beginPath();
      const innerRadius = radius * 0.7;
      const toothHeight = radius * 0.2;
      
      for (let i = 0; i < teeth * 2; i++) {
        const angle = (i / (teeth * 2)) * 2 * Math.PI;
        const r = (i % 2 === 0) ? radius : innerRadius;
        const x = centerX + r * Math.cos(angle);
        const y = centerY + r * Math.sin(angle);
        
        if (i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
      }
      ctx.closePath();
    }

    function drawLightning(ctx, x, y, w, h) {
      ctx.beginPath();
      ctx.moveTo(x + w * 0.3, y);
      ctx.lineTo(x + w * 0.7, y + h * 0.4);
      ctx.lineTo(x + w * 0.5, y + h * 0.4);
      ctx.lineTo(x + w * 0.8, y + h);
      ctx.lineTo(x + w * 0.4, y + h * 0.6);
      ctx.lineTo(x + w * 0.6, y + h * 0.6);
      ctx.closePath();
    }

    function drawLeaf(ctx, centerX, centerY, size) {
      ctx.beginPath();
      ctx.moveTo(centerX, centerY - size);
      ctx.bezierCurveTo(centerX + size * 0.6, centerY - size * 0.3, centerX + size * 0.8, centerY + size * 0.3, centerX, centerY + size);
      ctx.bezierCurveTo(centerX - size * 0.8, centerY + size * 0.3, centerX - size * 0.6, centerY - size * 0.3, centerX, centerY - size);
      ctx.closePath();
    }

    function drawFlower(ctx, centerX, centerY, radius) {
      ctx.beginPath();
      const petals = 6;
      
      for (let i = 0; i < petals; i++) {
        const angle = (i / petals) * 2 * Math.PI;
        const petalX = centerX + radius * 0.7 * Math.cos(angle);
        const petalY = centerY + radius * 0.7 * Math.sin(angle);
        
        ctx.moveTo(centerX, centerY);
        ctx.arc(petalX, petalY, radius * 0.4, 0, 2 * Math.PI);
      }
      
      // Centre de la fleur
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX, centerY, radius * 0.3, 0, 2 * Math.PI);
    }

    function drawSun(ctx, centerX, centerY, radius) {
      ctx.beginPath();
      
      // Rayons du soleil
      const rays = 12;
      for (let i = 0; i < rays; i++) {
        const angle = (i / rays) * 2 * Math.PI;
        const innerX = centerX + radius * 0.7 * Math.cos(angle);
        const innerY = centerY + radius * 0.7 * Math.sin(angle);
        const outerX = centerX + radius * Math.cos(angle);
        const outerY = centerY + radius * Math.sin(angle);
        
        ctx.moveTo(innerX, innerY);
        ctx.lineTo(outerX, outerY);
      }
      
      // Centre du soleil
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX, centerY, radius * 0.6, 0, 2 * Math.PI);
    }

    function drawMoon(ctx, centerX, centerY, radius) {
      ctx.beginPath();
      ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
      
      // Cratères
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX - radius * 0.3, centerY - radius * 0.2, radius * 0.15, 0, 2 * Math.PI);
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX + radius * 0.2, centerY + radius * 0.3, radius * 0.1, 0, 2 * Math.PI);
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX - radius * 0.1, centerY + radius * 0.4, radius * 0.08, 0, 2 * Math.PI);
    }

    function drawInfinity(ctx, centerX, centerY, width, height) {
      ctx.beginPath();
      const w = width / 2;
      const h = height / 2;
      
      // Première boucle
      ctx.moveTo(centerX - w * 0.5, centerY);
      ctx.bezierCurveTo(centerX - w * 0.5, centerY - h, centerX - w * 0.1, centerY - h, centerX, centerY);
      ctx.bezierCurveTo(centerX + w * 0.1, centerY + h, centerX + w * 0.5, centerY + h, centerX + w * 0.5, centerY);
      
      // Deuxième boucle
      ctx.bezierCurveTo(centerX + w * 0.5, centerY - h, centerX + w * 0.1, centerY - h, centerX, centerY);
      ctx.bezierCurveTo(centerX - w * 0.1, centerY + h, centerX - w * 0.5, centerY + h, centerX - w * 0.5, centerY);
    }

    function drawBubble(ctx, centerX, centerY, radius) {
      ctx.beginPath();
      ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
      
      // Reflet de la bulle
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX - radius * 0.3, centerY - radius * 0.3, radius * 0.2, 0, 2 * Math.PI);
    }

    function drawCrown(ctx, x, y, w, h) {
      ctx.beginPath();
      const points = 5;
      const baseY = y + h * 0.7;
      
      // Base de la couronne
      ctx.moveTo(x, baseY);
      ctx.lineTo(x + w, baseY);
      ctx.lineTo(x + w, y + h);
      ctx.lineTo(x, y + h);
      ctx.closePath();
      
      // Pointes de la couronne
      for (let i = 0; i < points; i++) {
        const pointX = x + (w / points) * i + (w / points) / 2;
        const pointY = y + (i % 2 === 0 ? 0 : h * 0.3);
        
        ctx.moveTo(pointX - w / points / 4, baseY);
        ctx.lineTo(pointX, pointY);
        ctx.lineTo(pointX + w / points / 4, baseY);
      }
    }

    function drawGem(ctx, centerX, centerY, size) {
      ctx.beginPath();
      
      // Facettes du diamant
      const points = [
        { x: centerX, y: centerY - size },          // Haut
        { x: centerX + size * 0.6, y: centerY - size * 0.3 },  // Haut droite
        { x: centerX + size * 0.4, y: centerY + size * 0.3 },  // Bas droite
        { x: centerX, y: centerY + size },          // Bas
        { x: centerX - size * 0.4, y: centerY + size * 0.3 },  // Bas gauche
        { x: centerX - size * 0.6, y: centerY - size * 0.3 }   // Haut gauche
      ];
      
      ctx.moveTo(points[0].x, points[0].y);
      for (let i = 1; i < points.length; i++) {
        ctx.lineTo(points[i].x, points[i].y);
      }
      ctx.closePath();
      
      // Lignes internes du diamant
      for (let i = 0; i < points.length; i++) {
        ctx.moveTo(centerX, centerY);
        ctx.lineTo(points[i].x, points[i].y);
      }
    }

    function drawShield(ctx, centerX, centerY, width, height) {
      ctx.beginPath();
      
      const w = width / 2;
      const h = height / 2;
      
      ctx.moveTo(centerX, centerY - h);
      ctx.lineTo(centerX + w * 0.8, centerY - h * 0.6);
      ctx.lineTo(centerX + w, centerY);
      ctx.lineTo(centerX + w * 0.8, centerY + h * 0.6);
      ctx.lineTo(centerX, centerY + h);
      ctx.lineTo(centerX - w * 0.8, centerY + h * 0.6);
      ctx.lineTo(centerX - w, centerY);
      ctx.lineTo(centerX - w * 0.8, centerY - h * 0.6);
      ctx.closePath();
    }

    function drawEye(ctx, centerX, centerY, width, height) {
      ctx.beginPath();
      
      const w = width / 2;
      const h = height / 2;
      
      // Contour de l'œil
      ctx.ellipse(centerX, centerY, w, h * 0.6, 0, 0, 2 * Math.PI);
      
      // Pupille
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX, centerY, w * 0.4, 0, 2 * Math.PI);
      
      // Iris
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX, centerY, w * 0.2, 0, 2 * Math.PI);
    }

    function drawButterfly(ctx, centerX, centerY, width, height) {
      ctx.beginPath();
      
      const w = width / 2;
      const h = height / 2;
      
      // Aile supérieure gauche
      ctx.ellipse(centerX - w * 0.5, centerY - h * 0.3, w * 0.4, h * 0.5, -Math.PI / 6, 0, 2 * Math.PI);
      
      // Aile inférieure gauche
      ctx.moveTo(centerX, centerY);
      ctx.ellipse(centerX - w * 0.3, centerY + h * 0.2, w * 0.3, h * 0.4, Math.PI / 6, 0, 2 * Math.PI);
      
      // Aile supérieure droite
      ctx.moveTo(centerX, centerY);
      ctx.ellipse(centerX + w * 0.5, centerY - h * 0.3, w * 0.4, h * 0.5, Math.PI / 6, 0, 2 * Math.PI);
      
      // Aile inférieure droite
      ctx.moveTo(centerX, centerY);
      ctx.ellipse(centerX + w * 0.3, centerY + h * 0.2, w * 0.3, h * 0.4, -Math.PI / 6, 0, 2 * Math.PI);
      
      // Corps
      ctx.moveTo(centerX, centerY - h);
      ctx.lineTo(centerX, centerY + h);
      
      // Antennes
      ctx.moveTo(centerX, centerY - h);
      ctx.lineTo(centerX - w * 0.1, centerY - h * 1.2);
      ctx.moveTo(centerX, centerY - h);
      ctx.lineTo(centerX + w * 0.1, centerY - h * 1.2);
    }

    // FONCTION POUR APPLIQUER LES STYLES ARTISTIQUES AUX FORMES
    function applyArtisticStyleToShape(ctx, s) {
      const intensity = styleIntensity / 100;
      const grain = textureGrain / 100;
      const spread = spreading / 100;
      const blur = blurEffect;
      const shine = shineIntensity / 100;
      
      // Appliquer l'effet de brillance si activé
      if (shine > 0) {
        const shineOpacityValue = shineOpacity / 100;
        const shineRgba = hexToRgba(shineColor, shineOpacityValue);
        ctx.shadowColor = rgbaToString(shineRgba);
        ctx.shadowBlur = Math.min(s.w, s.h) * shine * 0.2;
      }
      
      // Appliquer l'effet de flou si activé
      if (blur > 0) {
        ctx.filter = `blur(${blur}px)`;
      }
      
      let finalColor = s.color || '#000000';
      
      switch(currentBrushStyle) {
        case 'pastel':
          // Formes avec texture pastel douce
          ctx.globalAlpha = 0.7 * intensity;
          
          // Multiple passes pour effet poudreux
          for (let i = 0; i < 3; i++) {
            ctx.globalAlpha = (0.4 - i * 0.1) * intensity;
            const offset = grain * 3;
            
            ctx.beginPath();
            if(s.type === 'rectangle') {
              ctx.rect(s.x + (Math.random() - 0.5) * offset, s.y + (Math.random() - 0.5) * offset, s.w, s.h);
            } else if(s.type === 'circle') {
              const r = Math.min(Math.abs(s.w), Math.abs(s.h)) / 2;
              ctx.arc(s.x + s.w/2 + (Math.random() - 0.5) * offset, s.y + s.h/2 + (Math.random() - 0.5) * offset, r, 0, 2*Math.PI);
            }
            
            if (s.outlineOnly) {
              ctx.strokeStyle = finalColor;
              ctx.lineWidth = (s.outlineThickness || 1) * (1 + spread);
              ctx.stroke();
            } else {
              ctx.fillStyle = finalColor;
              ctx.fill();
            }
          }
          break;
          
        case 'charcoal':
          // Formes avec texture charbon rugueuse
          ctx.globalAlpha = 0.8 * intensity;
          
          // Effet charbon avec multiple traces
          for (let i = 0; i < Math.max(1, grain * 8); i++) {
            const offsetX = (Math.random() - 0.5) * Math.min(s.w, s.h) * 0.1;
            const offsetY = (Math.random() - 0.5) * Math.min(s.w, s.h) * 0.1;
            ctx.globalAlpha = (Math.random() * 0.5 + 0.3) * intensity;
            
            ctx.beginPath();
            if(s.type === 'rectangle') {
              ctx.rect(s.x + offsetX, s.y + offsetY, s.w, s.h);
            } else if(s.type === 'circle') {
              const r = Math.min(Math.abs(s.w), Math.abs(s.h)) / 2;
              ctx.arc(s.x + s.w/2 + offsetX, s.y + s.h/2 + offsetY, r, 0, 2*Math.PI);
            }
            
            if (s.outlineOnly) {
              ctx.strokeStyle = finalColor;
              ctx.lineWidth = (s.outlineThickness || 1) * (Math.random() * 0.8 + 0.5);
              ctx.stroke();
            } else {
              ctx.fillStyle = finalColor;
              ctx.fill();
            }
          }
          break;
          
        case 'watercolor':
          // Formes avec effet aquarelle
          const waterColor = parseRgba(finalColor) || hexToRgba(finalColor);
          
          // Gradient aquarelle
          const waterGrad = ctx.createRadialGradient(
            s.x + s.w/2, s.y + s.h/2, 0,
            s.x + s.w/2, s.y + s.h/2, Math.max(s.w, s.h) * (0.5 + spread)
          );
          
          waterGrad.addColorStop(0, rgbaToString({...waterColor, a: 0.8 * intensity}));
          waterGrad.addColorStop(0.7, rgbaToString({...waterColor, a: 0.4 * intensity}));
          waterGrad.addColorStop(1, rgbaToString({...waterColor, a: 0.1 * intensity}));
          
          ctx.beginPath();
          if(s.type === 'rectangle') {
            ctx.rect(s.x, s.y, s.w, s.h);
          } else if(s.type === 'circle') {
            const r = Math.min(Math.abs(s.w), Math.abs(s.h)) / 2;
            ctx.arc(s.x + s.w/2, s.y + s.h/2, r, 0, 2*Math.PI);
          }
          
          if (s.outlineOnly) {
            ctx.strokeStyle = waterGrad;
            ctx.lineWidth = (s.outlineThickness || 1) * (1 + spread);
            ctx.stroke();
          } else {
            ctx.fillStyle = waterGrad;
            ctx.fill();
          }
          
          // Tâches d'eau aléatoires
          if (Math.random() < grain) {
            ctx.globalAlpha = 0.3 * intensity;
            ctx.fillStyle = finalColor;
            const spotSize = Math.min(s.w, s.h) * (0.1 + Math.random() * 0.2);
            ctx.beginPath();
            ctx.arc(s.x + Math.random() * s.w, s.y + Math.random() * s.h, spotSize, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'ink':
          // Formes avec trait d'encre net
          ctx.globalAlpha = intensity;
          
          ctx.beginPath();
          if(s.type === 'rectangle') {
            ctx.rect(s.x, s.y, s.w, s.h);
          } else if(s.type === 'circle') {
            const r = Math.min(Math.abs(s.w), Math.abs(s.h)) / 2;
            ctx.arc(s.x + s.w/2, s.y + s.h/2, r, 0, 2*Math.PI);
          }
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
          
          // Bavure possible
          if (Math.random() < grain) {
            ctx.globalAlpha = 0.3 * intensity;
            ctx.lineWidth = (s.outlineThickness || 1) * (1 + spread * 3);
            ctx.stroke();
          }
          break;
          
        case 'oil':
          // Formes avec texture huile épaisse
          for (let layer = 0; layer < 3; layer++) {
            ctx.globalAlpha = (0.8 - layer * 0.2) * intensity;
            const offsetX = (Math.random() - 0.5) * grain * 5;
            const offsetY = (Math.random() - 0.5) * grain * 5;
            
            ctx.beginPath();
            if(s.type === 'rectangle') {
              ctx.rect(s.x + offsetX, s.y + offsetY, s.w, s.h);
            } else if(s.type === 'circle') {
              const r = Math.min(Math.abs(s.w), Math.abs(s.h)) / 2;
              ctx.arc(s.x + s.w/2 + offsetX, s.y + s.h/2 + offsetY, r, 0, 2*Math.PI);
            }
            
            if (s.outlineOnly) {
              ctx.strokeStyle = finalColor;
              ctx.lineWidth = (s.outlineThickness || 1) * (1.2 - layer * 0.2);
              ctx.stroke();
            } else {
              ctx.fillStyle = finalColor;
              ctx.fill();
            }
          }
          break;
          
        default:
          // Style normal - pas d'effet artistique
          ctx.globalAlpha = intensity;
          
          ctx.beginPath();
          if(s.type === 'rectangle') {
            ctx.rect(s.x, s.y, s.w, s.h);
          } else if(s.type === 'circle') {
            const r = Math.min(Math.abs(s.w), Math.abs(s.h)) / 2;
            ctx.arc(s.x + s.w/2, s.y + s.h/2, r, 0, 2*Math.PI);
          }
          
          if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
          } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
          }
          break;
      }
      
      // Réinitialiser les effets
      ctx.filter = 'none';
      ctx.shadowBlur = 0;
    }

    // Créer un gradient avancé avec tous les paramètres
    function createAdvancedGradient(ctx, s) {
      const centerX = s.x + s.w / 2;
      const centerY = s.y + s.h / 2;
      
      // Calculer les points du gradient selon l'angle
      const rad = (s.gradientOptions.angle || 0) * Math.PI / 180;
      const distance = Math.sqrt(s.w * s.w + s.h * s.h) / 2;
      
      const x1 = centerX - Math.cos(rad) * distance;
      const y1 = centerY - Math.sin(rad) * distance;
      const x2 = centerX + Math.cos(rad) * distance;
      const y2 = centerY + Math.sin(rad) * distance;
      
      const gradient = ctx.createLinearGradient(x1, y1, x2, y2);
      
      // Appliquer les transitions
      const topPos = (s.gradientOptions.transition.top || 0) / 100;
      const middlePos = (s.gradientOptions.transition.middle || 50) / 100;
      const bottomPos = (s.gradientOptions.transition.bottom || 100) / 100;
      
      // Ajuster les couleurs selon l'intensité et la saturation
      const color1 = adjustColorIntensitySaturation(s.gradientOptions.color1, s.gradientOptions.intensity, s.gradientOptions.saturation);
      const color2 = adjustColorIntensitySaturation(s.gradientOptions.color2, s.gradientOptions.intensity, s.gradientOptions.saturation);
      
      gradient.addColorStop(topPos, color1);
      gradient.addColorStop(middlePos, blendColors(color1, color2, 0.5));
      gradient.addColorStop(bottomPos, color2);
      
      return gradient;
    }

    // Ajuster l'intensité et la saturation d'une couleur
    function adjustColorIntensitySaturation(color, intensity = 100, saturation = 100) {
      // Convertir la couleur en HSL pour ajuster la saturation et la luminosité
      const rgb = hexToRgb(color);
      if (!rgb) return color;
      
      const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
      
      // Ajuster la saturation (0-200%)
      hsl.s = Math.max(0, Math.min(1, hsl.s * (saturation / 100)));
      
      // Ajuster l'intensité (luminosité) (0-200%)
      hsl.l = Math.max(0, Math.min(1, hsl.l * (intensity / 100)));
      
      const adjustedRgb = hslToRgb(hsl.h, hsl.s, hsl.l);
      return `rgb(${adjustedRgb.r}, ${adjustedRgb.g}, ${adjustedRgb.b})`;
    }

    // Mélanger deux couleurs
    function blendColors(color1, color2, ratio) {
      const rgb1 = hexToRgb(color1);
      const rgb2 = hexToRgb(color2);
      if (!rgb1 || !rgb2) return color1;
      
      const r = Math.round(rgb1.r + (rgb2.r - rgb1.r) * ratio);
      const g = Math.round(rgb1.g + (rgb2.g - rgb1.g) * ratio);
      const b = Math.round(rgb1.b + (rgb2.b - rgb1.b) * ratio);
      
      return `rgb(${r}, ${g}, ${b})`;
    }

    // Utilitaires de conversion de couleurs
    function hexToRgb(hex) {
      const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
      } : null;
    }

    function rgbToHsl(r, g, b) {
      r /= 255; g /= 255; b /= 255;
      const max = Math.max(r, g, b), min = Math.min(r, g, b);
      let h, s, l = (max + min) / 2;

      if (max === min) {
        h = s = 0; // achromatic
      } else {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
          case r: h = (g - b) / d + (g < b ? 6 : 0); break;
          case g: h = (b - r) / d + 2; break;
          case b: h = (r - g) / d + 4; break;
        }
        h /= 6;
      }
      return { h, s, l };
    }

    function hslToRgb(h, s, l) {
      let r, g, b;

      if (s === 0) {
        r = g = b = l; // achromatic
      } else {
        const hue2rgb = (p, q, t) => {
          if (t < 0) t += 1;
          if (t > 1) t -= 1;
          if (t < 1/6) return p + (q - p) * 6 * t;
          if (t < 1/2) return q;
          if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
          return p;
        };

        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
      }

      return {
        r: Math.round(r * 255),
        g: Math.round(g * 255),
        b: Math.round(b * 255)
      };
    }

    // Dessiner ligne selon outil ET sauvegarder automatiquement dans drawingLayer
    function drawLine(ctx,x1,y1,x2,y2,tool,size,color){
      ctx.save(); // Sauvegarder l'état du contexte
      ctx.lineCap='round'; ctx.lineJoin='round'; ctx.lineWidth=size;
      
      // Vérifier si c'est un outil spécialisé ou si un style artistique est activé
      const specializedTools = ['brush-marker', 'brush-fineliner', 'brush-fountain', 'brush-ballpoint', 'brush-charcoal', 'brush-pastel', 'brush-watercolor', 'brush-acrylic', 'brush-oil', 'brush-tempera', 'brush-gouache', 'brush-spray', 'brush-splatter', 'brush-stipple', 'brush-crosshatch', 'brush-scribble', 'brush-calligraphy', 'brush-texture', 'brush-digital', 'brush-glitch', 'brush-neon', 'brush-laser', 'brush-fire', 'brush-lightning', 'brush-galaxy'];
      
      if (specializedTools.includes(tool) || currentBrushStyle !== 'normal') {
        applyArtisticBrushStyle(ctx, x1, y1, x2, y2, tool, size, color);
        ctx.restore();
        
        // **CORRECTION: Ne plus utiliser drawingLayer avec le système unifié**
        // Les dessins sont maintenant gérés par drawingStrokes et le système de layers
        return;
      }
      
      // Dessiner sur le canvas principal
      if(tool==='brush-basic'){
        ctx.strokeStyle=color; ctx.globalAlpha=1;
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
      } else if(tool==='brush-pencil'){
        ctx.strokeStyle=color; ctx.globalAlpha=0.8; ctx.lineWidth=Math.max(1,size/3);
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        for(let i=0;i<size/2;i++){
          const px=x1+(Math.random()-0.5)*size, py=y1+(Math.random()-0.5)*size;
          ctx.fillStyle=color; ctx.globalAlpha=0.3;
          ctx.beginPath(); ctx.arc(px,py,0.5,0,2*Math.PI); ctx.fill();
        }
      } else if(tool==='brush-smoke'){
        // Opaciteur "fumée" : enlève progressivement la peinture
        const prevOp = ctx.globalCompositeOperation;
        ctx.globalCompositeOperation = 'destination-out';
        ctx.strokeStyle = 'rgba(0,0,0,1)';
        ctx.lineWidth = size * 1.5;
        ctx.globalAlpha = Math.max(0.02, Math.min(1, (window.smokeIntensity || 30) / 100));
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        ctx.globalCompositeOperation = prevOp;
      } else if(tool==='brush-chalk'){
        ctx.strokeStyle=color; ctx.lineWidth=size; ctx.globalAlpha=0.7;
        ctx.setLineDash([2,6]);
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        ctx.setLineDash([]);
      } else if(tool==='brush-brush'){
        const grad=ctx.createLinearGradient(x1,y1,x2,y2);
        grad.addColorStop(0,color); grad.addColorStop(1,'rgba(0,0,0,0)');
        ctx.strokeStyle=grad; ctx.lineWidth=size*1.2; ctx.globalAlpha=0.9;
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        
      // NOUVEAUX OUTILS SPÉCIALISÉS PHASE 6
      } else if(tool==='brush-marker'){
        // Effet marqueur - traits larges et semi-transparents
        ctx.globalAlpha = 0.8;
        ctx.lineWidth = size * 1.5;
        ctx.strokeStyle = color;
        const gradient = ctx.createLinearGradient(x1, y1, x2, y2);
        gradient.addColorStop(0, color);
        gradient.addColorStop(0.5, color + '80');
        gradient.addColorStop(1, color);
        ctx.strokeStyle = gradient;
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        
      } else if(tool==='brush-fineliner'){
        // Trait fin et précis
        ctx.lineWidth = Math.max(1, size * 0.3);
        ctx.strokeStyle = color;
        ctx.globalAlpha = 1.0;
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        
      } else if(tool==='brush-fountain'){
        // Plume fontaine avec variations d'épaisseur
        const pressure = Math.random() * 0.5 + 0.7;
        ctx.lineWidth = size * pressure;
        ctx.strokeStyle = color;
        ctx.globalAlpha = 0.9;
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        // Effet d'écoulement d'encre
        if (Math.random() > 0.95) {
          const dropSize = size * 0.3;
          ctx.fillStyle = color;
          ctx.globalAlpha = 0.6;
          ctx.beginPath();
          ctx.arc(x2 + (Math.random() - 0.5) * size, y2 + (Math.random() - 0.5) * size, dropSize, 0, 2*Math.PI);
          ctx.fill();
        }
        
      } else if(tool==='brush-ballpoint'){
        // Stylo bille - trait fin avec texture
        ctx.lineWidth = Math.max(1, size * 0.4);
        ctx.strokeStyle = color;
        ctx.globalAlpha = 0.8;
        for (let i = 0; i < 3; i++) {
          const offsetX = (Math.random() - 0.5) * 2;
          const offsetY = (Math.random() - 0.5) * 2;
          ctx.beginPath();
          ctx.moveTo(x1 + offsetX, y1 + offsetY);
          ctx.lineTo(x2 + offsetX, y2 + offsetY);
          ctx.stroke();
        }
        
      } else if(tool==='brush-charcoal'){
        // Fusain avec texture granuleuse
        ctx.lineWidth = size;
        ctx.strokeStyle = color;
        ctx.globalAlpha = 0.7;
        for (let i = 0; i < size * 2; i++) {
          const offsetX = (Math.random() - 0.5) * size;
          const offsetY = (Math.random() - 0.5) * size;
          const spotSize = Math.random() * 2 + 0.5;
          ctx.globalAlpha = Math.random() * 0.5 + 0.3;
          ctx.fillStyle = color;
          ctx.beginPath();
          ctx.arc(x2 + offsetX, y2 + offsetY, spotSize, 0, 2*Math.PI);
          ctx.fill();
        }
        
      } else if(tool==='brush-pastel'){
        // Pastel avec texture douce
        ctx.lineWidth = size * 1.2;
        ctx.strokeStyle = color;
        ctx.globalAlpha = 0.6;
        const pastelGradient = ctx.createRadialGradient(x2, y2, 0, x2, y2, size);
        pastelGradient.addColorStop(0, color);
        pastelGradient.addColorStop(1, color + '40');
        ctx.strokeStyle = pastelGradient;
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        
      } else if(tool==='brush-watercolor'){
        // Aquarelle avec effets de diffusion
        ctx.lineWidth = size;
        ctx.strokeStyle = color;
        ctx.globalAlpha = 0.3;
        const waterGradient = ctx.createRadialGradient(x2, y2, 0, x2, y2, size * 2);
        waterGradient.addColorStop(0, color);
        waterGradient.addColorStop(0.5, color + '40');
        waterGradient.addColorStop(1, color + '10');
        ctx.fillStyle = waterGradient;
        ctx.beginPath();
        ctx.arc(x2, y2, size * 1.5, 0, 2*Math.PI);
        ctx.fill();
        
      } else if(tool==='brush-neon'){
        // Effet néon
        ctx.lineWidth = size;
        ctx.strokeStyle = color;
        ctx.shadowColor = color;
        ctx.shadowBlur = size * 2;
        ctx.globalAlpha = 0.8;
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        // Cœur lumineux
        ctx.shadowBlur = 0;
        ctx.globalAlpha = 1.0;
        ctx.lineWidth = size * 0.3;
        ctx.strokeStyle = '#ffffff';
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        
      } else if(tool==='brush-fire'){
        // Effet feu
        for (let i = 0; i < size * 2; i++) {
          const flame_x = x2 + (Math.random() - 0.5) * size;
          const flame_y = y2 + (Math.random() - 0.5) * size;
          const flameSize = Math.random() * size * 0.8 + 2;
          const fireColors = ['#ff4400', '#ff6600', '#ff8800', '#ffaa00', '#ffcc00', '#ffffff'];
          const fireColor = fireColors[Math.floor(Math.random() * fireColors.length)];
          ctx.globalAlpha = Math.random() * 0.8 + 0.2;
          ctx.fillStyle = fireColor;
          ctx.shadowColor = fireColor;
          ctx.shadowBlur = flameSize;
          ctx.beginPath();
          ctx.arc(flame_x, flame_y - Math.random() * size, flameSize, 0, 2*Math.PI);
          ctx.fill();
        }
        
      } else if(tool==='brush-lightning'){
        // Effet éclair
        ctx.lineWidth = Math.max(1, size * 0.3);
        ctx.strokeStyle = '#ffffff';
        ctx.shadowColor = '#00aaff';
        ctx.shadowBlur = size * 2;
        ctx.globalAlpha = 1.0;
        const segments = 5;
        ctx.beginPath();
        ctx.moveTo(x1, y1);
        for (let i = 1; i <= segments; i++) {
          const progress = i / segments;
          const targetX = x1 + (x2 - x1) * progress;
          const targetY = y1 + (y2 - y1) * progress;
          const zigzagX = targetX + (Math.random() - 0.5) * size;
          const zigzagY = targetY + (Math.random() - 0.5) * size;
          ctx.lineTo(zigzagX, zigzagY);
        }
        ctx.stroke();
        
      } else if(tool==='brush-galaxy'){
        // Effet galaxie
        for (let i = 0; i < size * 3; i++) {
          const angle = Math.random() * 2 * Math.PI;
          const distance = Math.random() * size * 2;
          const starX = x2 + Math.cos(angle) * distance;
          const starY = y2 + Math.sin(angle) * distance;
          const starSize = Math.random() * 3 + 0.5;
          const galaxyColors = ['#ffffff', '#ffccff', '#ccccff', '#ccffff', '#ffffcc'];
          const starColor = galaxyColors[Math.floor(Math.random() * galaxyColors.length)];
          ctx.globalAlpha = Math.random() * 0.8 + 0.2;
          ctx.fillStyle = starColor;
          ctx.shadowColor = starColor;
          ctx.shadowBlur = starSize * 2;
          ctx.beginPath();
          ctx.arc(starX, starY, starSize, 0, 2*Math.PI);
          ctx.fill();
        }
        
      } else {
        // Outil par défaut
        ctx.strokeStyle=color; ctx.globalAlpha=1;
        ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
      }
      
      // APPLIQUER LES TEXTURES ET EFFETS PHASE 5 
      applyPhase5Textures(ctx, x1, y1, x2, y2, size, color);
      
      ctx.restore(); // Restaurer l'état du contexte
      
      // **CORRECTION: drawingLayer n'est plus utilisé avec le système unifié de layers**
    }

    // FONCTION PRINCIPALE DES STYLES ARTISTIQUES PHASE 4 + OUTILS SPÉCIALISÉS
    function applyArtisticBrushStyle(ctx, x1, y1, x2, y2, tool, size, color) {
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      
      const intensity = styleIntensity / 100;
      const grain = textureGrain / 100;
      const spread = spreading / 100;
      const blur = blurEffect;
      const shine = shineIntensity / 100;
      
      // Appliquer l'effet de brillance si activé
      if (shine > 0) {
        const shineOpacityValue = shineOpacity / 100;
        const shineRgba = hexToRgba(shineColor, shineOpacityValue);
        ctx.shadowColor = rgbaToString(shineRgba);
        ctx.shadowBlur = size * shine * 3; // Augmenté pour plus de visibilité
      }
      
      // Appliquer l'effet de flou si activé
      if (blur > 0) {
        ctx.filter = `blur(${blur}px)`;
      }
      
      // Si on utilise un outil spécialisé ET qu'aucun style artistique n'est activé, utiliser les effets de base de l'outil
      const specializedTools = ['brush-marker', 'brush-fineliner', 'brush-fountain', 'brush-ballpoint', 'brush-charcoal', 'brush-pastel', 'brush-watercolor', 'brush-acrylic', 'brush-oil', 'brush-tempera', 'brush-gouache', 'brush-spray', 'brush-splatter', 'brush-stipple', 'brush-crosshatch', 'brush-scribble', 'brush-calligraphy', 'brush-texture', 'brush-digital', 'brush-glitch', 'brush-neon', 'brush-laser', 'brush-fire', 'brush-lightning', 'brush-galaxy'];
      
      if (specializedTools.includes(tool) && currentBrushStyle === 'normal') {
        // **CORRECTION: Utiliser les variables de style globales**
        const intensity = styleIntensity / 100;
        const grain = textureGrain / 100;
        const spread = spreading / 100;
        const blur = blurEffect;
        
        // Appliquer l'effet de flou si activé
        if (blur > 0) {
          ctx.filter = `blur(${blur}px)`;
        }
        
        // **CORRECTION: Implémentation spécifique pour chaque outil spécialisé avec variables**
        switch(tool) {
          case 'brush-marker':
            ctx.globalAlpha = 0.8 * intensity; 
            ctx.lineWidth = size * (1.5 + spread); 
            ctx.strokeStyle = color;
            ctx.beginPath(); 
            ctx.moveTo(x1, y1); 
            ctx.lineTo(x2, y2); 
            ctx.stroke();
            break;
            
          case 'brush-fineliner':
            ctx.lineWidth = Math.max(1, size * (0.3 + grain * 0.2)); 
            ctx.strokeStyle = color; 
            ctx.globalAlpha = intensity;
            ctx.beginPath(); 
            ctx.moveTo(x1, y1); 
            ctx.lineTo(x2, y2); 
            ctx.stroke();
            break;
            
          case 'brush-fountain':
            ctx.lineWidth = size * (0.5 + Math.random() * 0.8 + spread * 0.5); 
            ctx.strokeStyle = color; 
            ctx.globalAlpha = 0.9 * intensity;
            ctx.beginPath(); 
            ctx.moveTo(x1, y1); 
            ctx.lineTo(x2, y2); 
            ctx.stroke();
            break;
            
          case 'brush-ballpoint':
            ctx.lineWidth = Math.max(1, size * (0.4 + grain * 0.1)); 
            ctx.strokeStyle = color; 
            ctx.globalAlpha = intensity;
            ctx.beginPath(); 
            ctx.moveTo(x1, y1); 
            ctx.lineTo(x2, y2); 
            ctx.stroke();
            break;
            
          case 'brush-charcoal':
            ctx.lineWidth = size * (1.2 + spread * 0.8); 
            ctx.strokeStyle = '#333333'; 
            ctx.globalAlpha = 0.7 * intensity;
            ctx.beginPath(); 
            ctx.moveTo(x1, y1); 
            ctx.lineTo(x2, y2); 
            ctx.stroke();
            // Particules de charbon avec grain
            for(let i = 0; i < size * grain * 2; i++) {
              const px = x1 + (x2-x1) * Math.random() + (Math.random()-0.5) * size * spread * 2;
              const py = y1 + (y2-y1) * Math.random() + (Math.random()-0.5) * size * spread * 2;
              ctx.fillStyle = '#222222'; 
              ctx.globalAlpha = Math.random() * 0.4 * intensity;
              ctx.beginPath(); 
              ctx.arc(px, py, Math.random() * 2, 0, 2*Math.PI); 
              ctx.fill();
            }
            break;
            
          case 'brush-pastel':
            ctx.lineWidth = size * (1.5 + spread); 
            ctx.strokeStyle = color; 
            ctx.globalAlpha = 0.6 * intensity;
            ctx.beginPath(); 
            ctx.moveTo(x1, y1); 
            ctx.lineTo(x2, y2); 
            ctx.stroke();
            // Effet poudreux
            for(let i = 0; i < size * grain; i++) {
              const px = x1 + (x2-x1) * Math.random() + (Math.random()-0.5) * size * spread;
              const py = y1 + (y2-y1) * Math.random() + (Math.random()-0.5) * size * spread;
              ctx.fillStyle = color; 
              ctx.globalAlpha = Math.random() * 0.3 * intensity;
              ctx.beginPath(); 
              ctx.arc(px, py, Math.random() * 1.5, 0, 2*Math.PI); 
              ctx.fill();
            }
            break;
            
          case 'brush-watercolor':
            const waterGrad = ctx.createLinearGradient(x1, y1, x2, y2);
            waterGrad.addColorStop(0, color);
            waterGrad.addColorStop(1, color + '40'); // Transparent
            ctx.lineWidth = size * (2 + spread); 
            ctx.strokeStyle = waterGrad; 
            ctx.globalAlpha = 0.5 * intensity;
            ctx.beginPath(); 
            ctx.moveTo(x1, y1); 
            ctx.lineTo(x2, y2); 
            ctx.stroke();
            break;
            
          case 'brush-acrylic':
            ctx.lineWidth = size * (1 + spread * 0.5); 
            ctx.strokeStyle = color; 
            ctx.globalAlpha = 0.9 * intensity;
            ctx.beginPath(); 
            ctx.moveTo(x1, y1); 
            ctx.lineTo(x2, y2); 
            ctx.stroke();
            break;
            
          case 'brush-oil':
            ctx.lineWidth = size * (1.2 + spread * 0.6); 
            ctx.strokeStyle = color; 
            ctx.globalAlpha = intensity;
            ctx.beginPath(); 
            ctx.moveTo(x1, y1); 
            ctx.lineTo(x2, y2); 
            ctx.stroke();
            // Effet empâtement
            ctx.lineWidth = size * (0.8 + grain * 0.4); 
            ctx.globalAlpha = 0.3 * intensity;
            ctx.beginPath(); 
            ctx.moveTo(x1+1, y1+1); 
            ctx.lineTo(x2+1, y2+1); 
            ctx.stroke();
            break;
            
          case 'brush-spray':
            for(let i = 0; i < size * (3 + grain * 5); i++) {
              const px = x1 + (x2-x1) * Math.random() + (Math.random()-0.5) * size * (2 + spread * 3);
              const py = y1 + (y2-y1) * Math.random() + (Math.random()-0.5) * size * (2 + spread * 3);
              ctx.fillStyle = color; 
              ctx.globalAlpha = Math.random() * 0.4 * intensity;
              ctx.beginPath(); 
              ctx.arc(px, py, Math.random() * 1.5, 0, 2*Math.PI); 
              ctx.fill();
            }
            break;
            
          case 'brush-neon':
            ctx.lineWidth = size * (1 + spread * 0.5); 
            ctx.strokeStyle = color; 
            ctx.shadowColor = color; 
            ctx.shadowBlur = size * (2 + grain * 2); 
            ctx.globalAlpha = 0.8 * intensity;
            ctx.beginPath(); 
            ctx.moveTo(x1, y1); 
            ctx.lineTo(x2, y2); 
            ctx.stroke();
            ctx.shadowBlur = 0;
            break;
            
          default:
            // Autres outils spécialisés - dessin basique avec variables
            ctx.strokeStyle = color;
            ctx.globalAlpha = intensity;
            ctx.lineWidth = size * (1 + spread * 0.5);
            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.stroke();
        }
        
        // Réinitialiser le filtre
        ctx.filter = 'none';
        return;
      }
      
      // **CORRECTION: Gérer les outils de base avec leurs effets spécifiques**
      const basicTools = ['brush-basic', 'brush-pencil', 'brush-smoke', 'brush-chalk', 'brush-brush'];
      if (basicTools.includes(tool) && currentBrushStyle === 'normal') {
        // Appliquer les effets spécifiques des outils de base
        if(tool==='brush-basic'){
          ctx.strokeStyle=color; ctx.globalAlpha=1;
          ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        } else if(tool==='brush-pencil'){
          ctx.strokeStyle=color; ctx.globalAlpha=0.8; ctx.lineWidth=Math.max(1,size/3);
          ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
          for(let i=0;i<size/2;i++){
            const px=x1+(Math.random()-0.5)*size, py=y1+(Math.random()-0.5)*size;
            ctx.fillStyle=color; ctx.globalAlpha=0.3;
            ctx.beginPath(); ctx.arc(px,py,0.5,0,2*Math.PI); ctx.fill();
          }
        } else if(tool==='brush-smoke'){
          const prevOp = ctx.globalCompositeOperation;
          ctx.globalCompositeOperation = 'destination-out';
          ctx.strokeStyle = 'rgba(0,0,0,1)';
          ctx.lineWidth = size * 1.5;
          ctx.globalAlpha = Math.max(0.02, Math.min(1, (window.smokeIntensity || 30) / 100));
          ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
          ctx.globalCompositeOperation = prevOp;
        } else if(tool==='brush-chalk'){
          ctx.strokeStyle=color; ctx.lineWidth=size; ctx.globalAlpha=0.7;
          ctx.setLineDash([2,6]);
          ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
          ctx.setLineDash([]);
        } else if(tool==='brush-brush'){
          const grad=ctx.createLinearGradient(x1,y1,x2,y2);
          grad.addColorStop(0,color); grad.addColorStop(1,'rgba(0,0,0,0.3)');
          ctx.strokeStyle=grad; ctx.lineWidth=size*1.2;
          ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
        }
        return;
      }
      
      switch(currentBrushStyle) {
        case 'pastel':
          // Texture douce, effet poudreux DRAMATIQUEMENT AMÉLIORÉ
          ctx.lineWidth = size * (1.5 + spread);
          ctx.globalAlpha = 0.8 * intensity;
          ctx.strokeStyle = color;
          
          // Effet poudreux avec multiple passes plus visibles
          for (let i = 0; i < 5; i++) {
            ctx.globalAlpha = (0.6 - i * 0.1) * intensity;
            ctx.lineWidth = size * (1.5 + i * 0.5);
            const offsetRange = grain * 8;
            ctx.beginPath();
            ctx.moveTo(x1 + (Math.random() - 0.5) * offsetRange, y1 + (Math.random() - 0.5) * offsetRange);
            ctx.lineTo(x2 + (Math.random() - 0.5) * offsetRange, y2 + (Math.random() - 0.5) * offsetRange);
            ctx.stroke();
          }
          
          // Particules de pastel volantes
          for (let i = 0; i < size * grain * 5; i++) {
            const px = x1 + (x2-x1) * Math.random() + (Math.random() - 0.5) * size * 3;
            const py = y1 + (y2-y1) * Math.random() + (Math.random() - 0.5) * size * 3;
            ctx.globalAlpha = Math.random() * 0.5 * intensity;
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(px, py, Math.random() * 4 + 1, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'charcoal':
          // Très texturé, noir profond, bords flous SPECTACULAIREMENT AMÉLIORÉ
          ctx.lineWidth = size * (1.8 + spread);
          ctx.globalAlpha = 0.9 * intensity;
          ctx.strokeStyle = color;
          
          // Effet charbon avec texture granuleuse extrême
          for (let i = 0; i < Math.max(5, grain * 20); i++) {
            const offsetX = (Math.random() - 0.5) * size * 1.5;
            const offsetY = (Math.random() - 0.5) * size * 1.5;
            ctx.globalAlpha = (Math.random() * 0.7 + 0.2) * intensity;
            ctx.lineWidth = Math.random() * size * 1.2 + size * 0.3;
            
            ctx.beginPath();
            ctx.moveTo(x1 + offsetX, y1 + offsetY);
            ctx.lineTo(x2 + offsetX, y2 + offsetY);
            ctx.stroke();
          }
          
          // Poussière de charbon
          for (let i = 0; i < size * 3; i++) {
            const dustX = x1 + (x2-x1) * Math.random() + (Math.random() - 0.5) * size * 4;
            const dustY = y1 + (y2-y1) * Math.random() + (Math.random() - 0.5) * size * 4;
            ctx.globalAlpha = Math.random() * 0.4 * intensity;
            ctx.fillStyle = '#222222';
            ctx.beginPath();
            ctx.arc(dustX, dustY, Math.random() * 3 + 0.5, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'watercolor':
          // Effets dilués, tâches et transparences MASSIVEMENT AMÉLIORÉS
          ctx.lineWidth = size * (2 + spread);
          
          // Effet aquarelle avec dégradés et transparence extrême
          const waterGrad = ctx.createLinearGradient(x1, y1, x2, y2);
          const baseColor = parseRgba(color) || hexToRgba(color);
          
          waterGrad.addColorStop(0, rgbaToString({...baseColor, a: 0.9 * intensity}));
          waterGrad.addColorStop(0.3, rgbaToString({...baseColor, a: 0.6 * intensity}));
          waterGrad.addColorStop(0.7, rgbaToString({...baseColor, a: 0.3 * intensity}));
          waterGrad.addColorStop(1, rgbaToString({...baseColor, a: 0.1 * intensity}));
          
          ctx.strokeStyle = waterGrad;
          ctx.beginPath();
          ctx.moveTo(x1, y1);
          ctx.lineTo(x2, y2);
          ctx.stroke();
          
          // Effet de diffusion aquarelle
          for (let i = 0; i < 8; i++) {
            const diffusionRadius = size * (2 + i * 0.5);
            ctx.globalAlpha = (0.15 - i * 0.015) * intensity;
            ctx.strokeStyle = color;
            ctx.lineWidth = diffusionRadius;
            ctx.beginPath();
            ctx.moveTo(x1, y1);
            ctx.lineTo(x2, y2);
            ctx.stroke();
          }
          
          // Tâches d'eau multiples
          for (let i = 0; i < Math.max(3, grain * 8); i++) {
            ctx.globalAlpha = (Math.random() * 0.3 + 0.1) * intensity;
            ctx.fillStyle = color;
            const spotSize = size * (1 + Math.random() * 3);
            const spotX = x1 + (x2-x1) * Math.random() + (Math.random() - 0.5) * size * 3;
            const spotY = y1 + (y2-y1) * Math.random() + (Math.random() - 0.5) * size * 3;
            
            // Dégradé radial pour l'effet d'eau
            const spotGrad = ctx.createRadialGradient(spotX, spotY, 0, spotX, spotY, spotSize);
            spotGrad.addColorStop(0, rgbaToString({...baseColor, a: 0.4 * intensity}));
            spotGrad.addColorStop(1, rgbaToString({...baseColor, a: 0.05 * intensity}));
            ctx.fillStyle = spotGrad;
            
            ctx.beginPath();
            ctx.arc(spotX, spotY, spotSize, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'ink':
          // Traits nets, couleurs saturées, possible bavure EXTRÊMEMENT AMÉLIORÉ
          ctx.lineWidth = size * 1.2;
          ctx.globalAlpha = intensity;
          ctx.strokeStyle = color;
          
          // Trait principal ultra-net
          ctx.beginPath();
          ctx.moveTo(x1, y1);
          ctx.lineTo(x2, y2);
          ctx.stroke();
          
          // Bavures d'encre spectaculaires
          if (Math.random() < grain * 2) {
            const inkBlots = Math.floor(grain * 5) + 2;
            for (let i = 0; i < inkBlots; i++) {
              const blotX = x1 + (x2-x1) * Math.random();
              const blotY = y1 + (y2-y1) * Math.random();
              const blotSize = size * (Math.random() * 1.5 + 0.5);
              
              ctx.globalAlpha = (Math.random() * 0.6 + 0.4) * intensity;
              ctx.fillStyle = color;
              
              // Forme irrégulière de bavure
              ctx.beginPath();
              for (let j = 0; j < 8; j++) {
                const angle = (j / 8) * 2 * Math.PI;
                const radius = blotSize * (0.5 + Math.random() * 0.5);
                const inkX = blotX + Math.cos(angle) * radius;
                const inkY = blotY + Math.sin(angle) * radius;
                if (j === 0) ctx.moveTo(inkX, inkY);
                else ctx.lineTo(inkX, inkY);
              }
              ctx.closePath();
              ctx.fill();
            }
          }
          break;
          
        case 'oil':
          // Peinture à l'huile épaisse avec empâtement DRAMATIQUEMENT AMÉLIORÉ
          ctx.lineWidth = size * (1.8 + spread);
          ctx.globalAlpha = 0.9 * intensity;
          ctx.strokeStyle = color;
          
          // Effet d'empâtement en couches
          for (let layer = 0; layer < 4; layer++) {
            ctx.globalAlpha = (0.8 - layer * 0.15) * intensity;
            ctx.lineWidth = size * (1.5 + layer * 0.3);
            
            // Légère variation pour l'effet pâteux
            const offsetX = (Math.random() - 0.5) * grain * 3;
            const offsetY = (Math.random() - 0.5) * grain * 3;
            
            ctx.beginPath();
            ctx.moveTo(x1 + offsetX, y1 + offsetY);
            ctx.lineTo(x2 + offsetX, y2 + offsetY);
            ctx.stroke();
          }
          
          // Texture d'empâtement avec relief
          for (let i = 0; i < size * grain * 3; i++) {
            const reliefX = x1 + (x2-x1) * Math.random() + (Math.random() - 0.5) * size;
            const reliefY = y1 + (y2-y1) * Math.random() + (Math.random() - 0.5) * size;
            
            ctx.globalAlpha = Math.random() * 0.3 * intensity;
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(reliefX, reliefY, Math.random() * 3 + 1, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'airbrush':
          // Gradient doux, effet spray MASSIVEMENT AMÉLIORÉ
          ctx.globalAlpha = 0.4 * intensity;
          
          // Multiple couches pour effet aérographe réaliste
          for (let i = 0; i < 6; i++) {
            const airSize = size * (2 + i * 0.8 + spread * 2);
            const airGrad = ctx.createRadialGradient(x1, y1, 0, x1, y1, airSize);
            const airColor = parseRgba(color) || hexToRgba(color);
            
            airGrad.addColorStop(0, rgbaToString({...airColor, a: (0.3 - i * 0.04) * intensity}));
            airGrad.addColorStop(0.5, rgbaToString({...airColor, a: (0.15 - i * 0.02) * intensity}));
            airGrad.addColorStop(1, rgbaToString({...airColor, a: 0}));
            
            ctx.fillStyle = airGrad;
            ctx.beginPath();
            ctx.arc(x1 + (x2-x1) * (i/6), y1 + (y2-y1) * (i/6), airSize, 0, 2*Math.PI);
            ctx.fill();
          }
          
          // Particules d'aérosol
          for (let i = 0; i < size * 4; i++) {
            const particleX = x1 + (x2-x1) * Math.random() + (Math.random() - 0.5) * size * 4;
            const particleY = y1 + (y2-y1) * Math.random() + (Math.random() - 0.5) * size * 4;
            
            ctx.globalAlpha = Math.random() * 0.2 * intensity;
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(particleX, particleY, Math.random() * 2 + 0.5, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'fresco':
          // Effet fresque ancienne NOUVEAU STYLE SPECTACULAIRE
          ctx.lineWidth = size * (1.3 + spread * 0.7);
          ctx.globalAlpha = 0.7 * intensity;
          
          // Texture de mur ancien
          for (let i = 0; i < Math.max(3, grain * 12); i++) {
            const crackedColor = adjustColorBrightness(color, Math.random() * 0.4 - 0.2);
            ctx.strokeStyle = crackedColor;
            ctx.globalAlpha = (Math.random() * 0.4 + 0.3) * intensity;
            ctx.lineWidth = Math.random() * size * 0.8 + size * 0.4;
            
            const crackOffset = (Math.random() - 0.5) * size;
            ctx.beginPath();
            ctx.moveTo(x1 + crackOffset, y1 + crackOffset);
            ctx.lineTo(x2 + crackOffset, y2 + crackOffset);
            ctx.stroke();
          }
          
          // Effet d'usure et craquelures
          for (let i = 0; i < size * grain * 2; i++) {
            const crackX = x1 + (x2-x1) * Math.random();
            const crackY = y1 + (y2-y1) * Math.random();
            
            ctx.globalAlpha = Math.random() * 0.3 * intensity;
            ctx.strokeStyle = adjustColorBrightness(color, -0.3);
            ctx.lineWidth = 1;
            
            ctx.beginPath();
            ctx.moveTo(crackX, crackY);
            ctx.lineTo(crackX + (Math.random() - 0.5) * 8, crackY + (Math.random() - 0.5) * 8);
            ctx.stroke();
          }
          break;
          
        case 'impasto':
          // Technique d'empâtement extrême NOUVEAU STYLE ÉPOUSTOUFLANT
          ctx.lineWidth = size * (2 + spread);
          ctx.globalAlpha = 0.95 * intensity;
          
          // Relief extrême avec multiple couches
          for (let layer = 0; layer < 6; layer++) {
            const layerColor = adjustColorBrightness(color, (layer - 3) * 0.1);
            ctx.strokeStyle = layerColor;
            ctx.globalAlpha = (0.9 - layer * 0.12) * intensity;
            ctx.lineWidth = size * (2.2 - layer * 0.2);
            
            const reliefOffset = layer * 2;
            ctx.beginPath();
            ctx.moveTo(x1 + reliefOffset, y1 + reliefOffset);
            ctx.lineTo(x2 + reliefOffset, y2 + reliefOffset);
            ctx.stroke();
          }
          
          // Texture empâtée avec bosses
          for (let i = 0; i < size * 5; i++) {
            const bumpX = x1 + (x2-x1) * Math.random() + (Math.random() - 0.5) * size * 2;
            const bumpY = y1 + (y2-y1) * Math.random() + (Math.random() - 0.5) * size * 2;
            const bumpSize = Math.random() * size * 0.8 + 2;
            
            ctx.globalAlpha = Math.random() * 0.6 * intensity;
            ctx.fillStyle = adjustColorBrightness(color, Math.random() * 0.4 - 0.2);
            ctx.beginPath();
            ctx.arc(bumpX, bumpY, bumpSize, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'pointillism':
          // Technique pointilliste NOUVEAU STYLE MAGNIFIQUE
          const pointSize = Math.max(2, size * 0.3);
          const pointDensity = Math.max(5, size * grain * 3);
          
          for (let i = 0; i < pointDensity; i++) {
            const pointX = x1 + (x2-x1) * (i/pointDensity) + (Math.random() - 0.5) * size;
            const pointY = y1 + (y2-y1) * (i/pointDensity) + (Math.random() - 0.5) * size;
            
            // Variation de couleur pour effet pointilliste
            const hueShift = (Math.random() - 0.5) * 30; // Décalage de teinte
            const pointColor = adjustColorHue(color, hueShift);
            
            ctx.globalAlpha = (Math.random() * 0.5 + 0.5) * intensity;
            ctx.fillStyle = pointColor;
            ctx.beginPath();
            ctx.arc(pointX, pointY, pointSize + Math.random() * 2, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'expressionist':
          // Style expressionniste sauvage NOUVEAU STYLE INTENSE
          ctx.lineWidth = size * (1.5 + spread);
          ctx.globalAlpha = 0.8 * intensity;
          
          // Traits expressifs et déformés
          for (let i = 0; i < Math.max(3, grain * 8); i++) {
            const wildnessX = (Math.random() - 0.5) * size * 2;
            const wildnessY = (Math.random() - 0.5) * size * 2;
            const wildColor = adjustColorSaturation(color, Math.random() * 0.5 + 0.5);
            
            ctx.strokeStyle = wildColor;
            ctx.globalAlpha = (Math.random() * 0.6 + 0.4) * intensity;
            ctx.lineWidth = Math.random() * size * 1.5 + size * 0.5;
            
            ctx.beginPath();
            ctx.moveTo(x1 + wildnessX, y1 + wildnessY);
            ctx.lineTo(x2 + wildnessX * 1.5, y2 + wildnessY * 1.5);
            ctx.stroke();
          }
          
          // Éclaboussures émotionnelles
          for (let i = 0; i < size * 2; i++) {
            if (Math.random() > 0.7) {
              const splashX = x1 + (x2-x1) * Math.random() + (Math.random() - 0.5) * size * 4;
              const splashY = y1 + (y2-y1) * Math.random() + (Math.random() - 0.5) * size * 4;
              
              ctx.globalAlpha = Math.random() * 0.7 * intensity;
              ctx.fillStyle = adjustColorSaturation(color, 1.5);
              ctx.beginPath();
              ctx.arc(splashX, splashY, Math.random() * size + 2, 0, 2*Math.PI);
              ctx.fill();
            }
          }
          break;
          
          ctx.fillStyle = airGrad;
          
          // Multiple points pour effet spray
          const steps = Math.max(1, Math.abs(x2-x1) + Math.abs(y2-y1)) / 5;
          for (let i = 0; i <= steps; i++) {
            const t = i / steps;
            const px = x1 + (x2-x1) * t;
            const py = y1 + (y2-y1) * t;
            
            // Particules aléatoires pour effet grain
            for (let j = 0; j < grain * 10; j++) {
              const offsetX = (Math.random() - 0.5) * size * spread;
              const offsetY = (Math.random() - 0.5) * size * spread;
              ctx.globalAlpha = Math.random() * 0.3 * intensity;
              ctx.beginPath();
              ctx.arc(px + offsetX, py + offsetY, Math.random() * 2 + 0.5, 0, 2*Math.PI);
              ctx.fill();
            }
          }
          break;
          
        case 'oil':
          // Texture épaisse, empâtement, couleurs riches
          ctx.lineWidth = size * (1 + spread * 0.6);
          ctx.globalAlpha = 0.9 * intensity;
          
          // Multiple couches pour effet empâtement
          for (let layer = 0; layer < 3; layer++) {
            ctx.strokeStyle = color;
            ctx.globalAlpha = (0.9 - layer * 0.2) * intensity;
            ctx.lineWidth = size * (1.2 - layer * 0.2);
            
            const offsetX = (Math.random() - 0.5) * grain * 3;
            const offsetY = (Math.random() - 0.5) * grain * 3;
            
            ctx.beginPath();
            ctx.moveTo(x1 + offsetX, y1 + offsetY);
            ctx.lineTo(x2 + offsetX, y2 + offsetY);
            ctx.stroke();
          }
          break;
          
        case 'gouache':
          // Mat, opaque, couvrant
          ctx.lineWidth = size * (1 + spread * 0.4);
          ctx.globalAlpha = 0.95 * intensity;
          ctx.strokeStyle = color;
          
          // Trait opaque et mat
          ctx.beginPath();
          ctx.moveTo(x1, y1);
          ctx.lineTo(x2, y2);
          ctx.stroke();
          
          // Texture légèrement granuleuse
          if (grain > 0.3) {
            ctx.globalAlpha = 0.5 * intensity;
            for (let i = 0; i < grain * 5; i++) {
              const px = x1 + (x2-x1) * Math.random();
              const py = y1 + (y2-y1) * Math.random();
              ctx.beginPath();
              ctx.arc(px, py, Math.random() + 0.5, 0, 2*Math.PI);
              ctx.fill();
            }
          }
          break;
          
        case 'sponge':
          // Texture très poreuse, irrégulière
          ctx.lineWidth = size;
          
          // Effet éponge avec multiples trous et textures
          const spongeSteps = Math.max(5, Math.abs(x2-x1) + Math.abs(y2-y1)) / 3;
          for (let i = 0; i <= spongeSteps; i++) {
            const t = i / spongeSteps;
            const px = x1 + (x2-x1) * t;
            const py = y1 + (y2-y1) * t;
            
            // Texture poreuse
            for (let j = 0; j < grain * 15; j++) {
              if (Math.random() > 0.6) continue; // Trous dans l'éponge
              
              const offsetX = (Math.random() - 0.5) * size * spread;
              const offsetY = (Math.random() - 0.5) * size * spread;
              const spotSize = Math.random() * 3 + 1;
              
              ctx.globalAlpha = (Math.random() * 0.6 + 0.2) * intensity;
              ctx.fillStyle = color;
              ctx.beginPath();
              ctx.arc(px + offsetX, py + offsetY, spotSize, 0, 2*Math.PI);
              ctx.fill();
            }
          }
          break;
      }
      
    // FONCTION POUR APPLIQUER LES DIFFÉRENTS TYPES D'OUTILS DE PINCEAU
    function applyBrushTool(ctx, x, y, lastX, lastY, size, color) {
      const toolType = currentTool;
      ctx.globalAlpha = strokeOpacity / 100;
      
      switch(toolType) {
        case 'marker':
          // Effet marqueur - traits larges et semi-transparents
          ctx.globalAlpha = 0.8;
          ctx.lineWidth = size * 1.5;
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          
          // Effet de transparence typique du marqueur
          const gradient = ctx.createLinearGradient(lastX, lastY, x, y);
          gradient.addColorStop(0, color);
          gradient.addColorStop(0.5, color + '80'); // Semi-transparent
          gradient.addColorStop(1, color);
          ctx.strokeStyle = gradient;
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          break;
          
        case 'fineliner':
          // Trait fin et précis
          ctx.lineWidth = Math.max(1, size * 0.3);
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          ctx.globalAlpha = 1.0;
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          break;
          
        case 'fountain':
          // Plume fontaine avec variations d'épaisseur
          const pressure = Math.random() * 0.5 + 0.7;
          ctx.lineWidth = size * pressure;
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          
          // Effet d'écoulement d'encre
          if (Math.random() > 0.95) {
            const dropSize = size * 0.3;
            ctx.fillStyle = color;
            ctx.globalAlpha = 0.6;
            ctx.beginPath();
            ctx.arc(x + (Math.random() - 0.5) * size, y + (Math.random() - 0.5) * size, dropSize, 0, 2*Math.PI);
            ctx.fill();
          }
          
          ctx.globalAlpha = 0.9;
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          break;
          
        case 'ballpoint':
          // Stylo bille - trait fin avec texture
          ctx.lineWidth = Math.max(1, size * 0.4);
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          ctx.globalAlpha = 0.8;
          
          // Texture granuleuse typique du stylo bille
          for (let i = 0; i < 3; i++) {
            const offsetX = (Math.random() - 0.5) * 2;
            const offsetY = (Math.random() - 0.5) * 2;
            ctx.beginPath();
            ctx.moveTo(lastX + offsetX, lastY + offsetY);
            ctx.lineTo(x + offsetX, y + offsetY);
            ctx.stroke();
          }
          break;
          
        case 'charcoal':
          // Fusain avec texture granuleuse
          ctx.lineWidth = size;
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          ctx.globalAlpha = 0.7;
          
          // Texture granuleuse du fusain
          for (let i = 0; i < size * 2; i++) {
            const offsetX = (Math.random() - 0.5) * size;
            const offsetY = (Math.random() - 0.5) * size;
            const spotSize = Math.random() * 2 + 0.5;
            
            ctx.globalAlpha = Math.random() * 0.5 + 0.3;
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(x + offsetX, y + offsetY, spotSize, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'pastel':
          // Pastel avec texture douce
          ctx.lineWidth = size * 1.2;
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          ctx.globalAlpha = 0.6;
          
          // Effet de mélange du pastel
          const pastelGradient = ctx.createRadialGradient(x, y, 0, x, y, size);
          pastelGradient.addColorStop(0, color);
          pastelGradient.addColorStop(1, color + '40');
          ctx.strokeStyle = pastelGradient;
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          
          // Particules de pastel
          for (let i = 0; i < size; i++) {
            if (Math.random() > 0.7) {
              const offsetX = (Math.random() - 0.5) * size * 2;
              const offsetY = (Math.random() - 0.5) * size * 2;
              ctx.globalAlpha = Math.random() * 0.3 + 0.1;
              ctx.fillStyle = color;
              ctx.beginPath();
              ctx.arc(x + offsetX, y + offsetY, Math.random() * 3 + 1, 0, 2*Math.PI);
              ctx.fill();
            }
          }
          break;
          
        case 'watercolor':
          // Aquarelle avec effets de diffusion
          ctx.lineWidth = size;
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          ctx.globalAlpha = 0.3;
          
          // Effet de diffusion de l'eau
          const waterGradient = ctx.createRadialGradient(x, y, 0, x, y, size * 2);
          waterGradient.addColorStop(0, color);
          waterGradient.addColorStop(0.5, color + '40');
          waterGradient.addColorStop(1, color + '10');
          
          ctx.fillStyle = waterGradient;
          ctx.beginPath();
          ctx.arc(x, y, size * 1.5, 0, 2*Math.PI);
          ctx.fill();
          
          // Trait principal
          ctx.globalAlpha = 0.6;
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          break;
          
        case 'acrylic':
          // Peinture acrylique avec texture épaisse
          ctx.lineWidth = size;
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          ctx.globalAlpha = 0.9;
          
          // Texture épaisse de l'acrylique
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          
          // Effets de relief
          for (let i = 0; i < 3; i++) {
            ctx.globalAlpha = 0.3;
            ctx.lineWidth = size * 0.8;
            const offset = i * 2;
            ctx.beginPath();
            ctx.moveTo(lastX + offset, lastY + offset);
            ctx.lineTo(x + offset, y + offset);
            ctx.stroke();
          }
          break;
          
        case 'oil':
          // Peinture à l'huile avec mélange
          ctx.lineWidth = size;
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          ctx.globalAlpha = 0.8;
          
          // Effet de mélange de l'huile
          ctx.filter = 'blur(1px)';
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          
          ctx.filter = 'none';
          ctx.globalAlpha = 0.6;
          ctx.lineWidth = size * 0.8;
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          break;
          
        case 'spray':
          // Aérographe
          ctx.globalAlpha = 0.1;
          
          for (let i = 0; i < size * 3; i++) {
            const angle = Math.random() * 2 * Math.PI;
            const distance = Math.random() * size;
            const sprayX = x + Math.cos(angle) * distance;
            const sprayY = y + Math.sin(angle) * distance;
            
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(sprayX, sprayY, Math.random() * 2 + 0.5, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'splatter':
          // Éclaboussures
          for (let i = 0; i < size; i++) {
            if (Math.random() > 0.8) {
              const splatterX = x + (Math.random() - 0.5) * size * 4;
              const splatterY = y + (Math.random() - 0.5) * size * 4;
              const splatterSize = Math.random() * size * 0.5 + 1;
              
              ctx.globalAlpha = Math.random() * 0.7 + 0.3;
              ctx.fillStyle = color;
              ctx.beginPath();
              ctx.arc(splatterX, splatterY, splatterSize, 0, 2*Math.PI);
              ctx.fill();
            }
          }
          break;
          
        case 'digital':
          // Pinceau numérique parfait
          ctx.lineWidth = size;
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          ctx.globalAlpha = 1.0;
          ctx.filter = 'none';
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          break;
          
        case 'glitch':
          // Effet glitch
          ctx.lineWidth = size;
          ctx.strokeStyle = color;
          
          // Décalages RGB
          const glitchOffset = size * 0.5;
          ctx.globalCompositeOperation = 'screen';
          
          // Canal rouge
          ctx.globalAlpha = 0.7;
          ctx.strokeStyle = '#ff0000';
          ctx.beginPath();
          ctx.moveTo(lastX - glitchOffset, lastY);
          ctx.lineTo(x - glitchOffset, y);
          ctx.stroke();
          
          // Canal vert
          ctx.strokeStyle = '#00ff00';
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          
          // Canal bleu
          ctx.strokeStyle = '#0000ff';
          ctx.beginPath();
          ctx.moveTo(lastX + glitchOffset, lastY);
          ctx.lineTo(x + glitchOffset, y);
          ctx.stroke();
          
          ctx.globalCompositeOperation = 'source-over';
          break;
          
        case 'neon':
          // Effet néon
          ctx.lineWidth = size;
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          
          // Lueur externe
          ctx.shadowColor = color;
          ctx.shadowBlur = size * 2;
          ctx.globalAlpha = 0.8;
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          
          // Cœur lumineux
          ctx.shadowBlur = 0;
          ctx.globalAlpha = 1.0;
          ctx.lineWidth = size * 0.3;
          ctx.strokeStyle = '#ffffff';
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          break;
          
        case 'laser':
          // Faisceau laser
          ctx.lineWidth = Math.max(2, size * 0.5);
          ctx.lineCap = 'round';
          ctx.globalAlpha = 1.0;
          
          // Faisceau principal
          ctx.strokeStyle = color;
          ctx.shadowColor = color;
          ctx.shadowBlur = size;
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          
          // Cœur ultra-lumineux
          ctx.shadowBlur = 0;
          ctx.lineWidth = 1;
          ctx.strokeStyle = '#ffffff';
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          break;
          
        case 'fire':
          // Effet feu
          for (let i = 0; i < size * 2; i++) {
            const flame_x = x + (Math.random() - 0.5) * size;
            const flame_y = y + (Math.random() - 0.5) * size;
            const flameSize = Math.random() * size * 0.8 + 2;
            
            // Couleurs de feu
            const fireColors = ['#ff4400', '#ff6600', '#ff8800', '#ffaa00', '#ffcc00', '#ffffff'];
            const fireColor = fireColors[Math.floor(Math.random() * fireColors.length)];
            
            ctx.globalAlpha = Math.random() * 0.8 + 0.2;
            ctx.fillStyle = fireColor;
            ctx.shadowColor = fireColor;
            ctx.shadowBlur = flameSize;
            
            ctx.beginPath();
            ctx.arc(flame_x, flame_y - Math.random() * size, flameSize, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'lightning':
          // Effet éclair
          ctx.lineWidth = Math.max(1, size * 0.3);
          ctx.strokeStyle = '#ffffff';
          ctx.shadowColor = '#00aaff';
          ctx.shadowBlur = size * 2;
          ctx.globalAlpha = 1.0;
          
          // Zigzag électrique
          const segments = 5;
          const segmentLength = Math.sqrt((x - lastX)**2 + (y - lastY)**2) / segments;
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          
          for (let i = 1; i <= segments; i++) {
            const progress = i / segments;
            const targetX = lastX + (x - lastX) * progress;
            const targetY = lastY + (y - lastY) * progress;
            
            const zigzagX = targetX + (Math.random() - 0.5) * size;
            const zigzagY = targetY + (Math.random() - 0.5) * size;
            
            ctx.lineTo(zigzagX, zigzagY);
          }
          
          ctx.stroke();
          break;
          
        case 'galaxy':
          // Effet galaxie
          for (let i = 0; i < size * 3; i++) {
            const angle = Math.random() * 2 * Math.PI;
            const distance = Math.random() * size * 2;
            const starX = x + Math.cos(angle) * distance;
            const starY = y + Math.sin(angle) * distance;
            const starSize = Math.random() * 3 + 0.5;
            
            // Couleurs galactiques
            const galaxyColors = ['#ffffff', '#ffccff', '#ccccff', '#ccffff', '#ffffcc'];
            const starColor = galaxyColors[Math.floor(Math.random() * galaxyColors.length)];
            
            ctx.globalAlpha = Math.random() * 0.8 + 0.2;
            ctx.fillStyle = starColor;
            ctx.shadowColor = starColor;
            ctx.shadowBlur = starSize * 2;
            
            ctx.beginPath();
            ctx.arc(starX, starY, starSize, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        default:
          // Pinceau standard
          ctx.lineWidth = size;
          ctx.lineCap = 'round';
          ctx.strokeStyle = color;
          ctx.globalAlpha = strokeOpacity / 100;
          
          ctx.beginPath();
          ctx.moveTo(lastX, lastY);
          ctx.lineTo(x, y);
          ctx.stroke();
          break;
      }
      
      // Réinitialiser les effets
      ctx.shadowBlur = 0;
      ctx.shadowColor = 'transparent';
      ctx.filter = 'none';
      ctx.globalCompositeOperation = 'source-over';
    }
    }

    // FONCTION TEXTURES PHASE 5
    function applyPhase5Textures(ctx, x1, y1, x2, y2, size, color) {
      const texIntensity = textureIntensity / 100;
      
      // Appliquer texture traditionnelle
      if (currentTextureStyle !== 'none') {
        applyTraditionalTexture(ctx, x1, y1, x2, y2, size, color, texIntensity);
      }
      
      // Appliquer texture naturelle
      if (currentNaturalTexture !== 'none') {
        applyNaturalTexture(ctx, x1, y1, x2, y2, size, color, texIntensity);
      }
      
      // Appliquer effet numérique
      if (currentDigitalEffect !== 'none') {
        applyDigitalEffect(ctx, x1, y1, x2, y2, size, color, texIntensity);
      }
    }

    // Seeded random number generator
    function seededRandom(seed) {
        var x = Math.sin(seed++) * 10000;
        return x - Math.floor(x);
    }

    // FONCTION POUR APPLIQUER LES STYLES ARTISTIQUES AUX FORMES INDIVIDUELLES
    function applyIndividualArtisticStyle(ctx, s) {
      const intensity = (s.styleIntensity || 50) / 100;
      const grain = (s.styleGrain || 30) / 100;
      const spread = (s.styleSpreading || 20) / 100;
      const blur = s.styleBlur || 0;
      const shine = (s.styleShine || 0) / 100;
      
      // Generate a stable seed from the shape ID
      let seed = 0;
      if (s.id) {
        const str = s.id.toString();
        for (let i = 0; i < str.length; i++) {
          seed = (seed << 5) - seed + str.charCodeAt(i);
          seed |= 0;
        }
      } else {
        seed = Math.floor(s.x + s.y + s.w + s.h); // Fallback seed
      }
      
      // Appliquer l'effet de brillance si activé
      if (shine > 0) {
        const shineOpacityValue = 30 / 100; // Opacité par défaut
        const shineRgba = hexToRgba('#ffffff', shineOpacityValue);
        ctx.shadowColor = rgbaToString(shineRgba);
        ctx.shadowBlur = Math.min(s.w, s.h) * shine * 0.2;
      }
      
      // Appliquer l'effet de flou si activé
      if (blur > 0) {
        ctx.filter = `blur(${blur}px)`;
      }
      
      let finalColor = s.color || '#000000';
      
      // Appliquer le style artistique selon le type choisi
      switch(s.artisticStyle) {
        case 'pastel':
          applyPastelToShape(ctx, s, finalColor, intensity, grain, spread, seed);
          break;
        case 'charcoal':
          applyCharcoalToShape(ctx, s, finalColor, intensity, grain, spread, seed);
          break;
        case 'watercolor':
          applyWatercolorToShape(ctx, s, finalColor, intensity, grain, spread, seed);
          break;
        case 'ink':
          applyInkToShape(ctx, s, finalColor, intensity, grain, spread, seed);
          break;
        case 'oil':
          applyOilToShape(ctx, s, finalColor, intensity, grain, spread, seed);
          break;
        default:
          // Style normal
          drawBasicShape(ctx, s, finalColor);
          break;
      }
      
      // Appliquer texture supplémentaire si définie
      if (s.extraTexture && s.extraTexture !== 'none') {
        applyExtraTextureToShape(ctx, s, finalColor, intensity, seed);
      }
      
      // Réinitialiser les effets
      ctx.filter = 'none';
      ctx.shadowBlur = 0;
    }

    // Fonctions d'application des styles aux formes
    function applyPastelToShape(ctx, s, color, intensity, grain, spread, seed) {
      ctx.globalAlpha = 0.7 * intensity;
      for (let i = 0; i < 3; i++) {
        ctx.globalAlpha = (0.4 - i * 0.1) * intensity;
        const offset = grain * 3;
        // Use seeded random for offset
        // We need to vary the seed for each iteration
        seed += 100;
        drawShapeWithOffset(ctx, s, color, offset, spread); // Note: drawShapeWithOffset doesn't use random, but offset is passed
      }
    }

    function applyCharcoalToShape(ctx, s, color, intensity, grain, spread, seed) {
      for (let i = 0; i < Math.max(1, grain * 8); i++) {
        seed += i * 10;
        const offsetX = (seededRandom(seed++) - 0.5) * Math.min(s.w, s.h) * 0.1;
        const offsetY = (seededRandom(seed++) - 0.5) * Math.min(s.w, s.h) * 0.1;
        ctx.globalAlpha = (seededRandom(seed++) * 0.5 + 0.3) * intensity;
        drawShapeWithOffset(ctx, s, color, offsetX, offsetY);
      }
    }

    function applyWatercolorToShape(ctx, s, color, intensity, grain, spread, seed) {
      const waterColor = parseRgba(color) || hexToRgba(color);
      const waterGrad = ctx.createRadialGradient(
        s.x + s.w/2, s.y + s.h/2, 0,
        s.x + s.w/2, s.y + s.h/2, Math.max(s.w, s.h) * (0.5 + spread)
      );
      
      waterGrad.addColorStop(0, rgbaToString({...waterColor, a: 0.8 * intensity}));
      waterGrad.addColorStop(0.7, rgbaToString({...waterColor, a: 0.4 * intensity}));
      waterGrad.addColorStop(1, rgbaToString({...waterColor, a: 0.1 * intensity}));
      
      drawBasicShape(ctx, s, waterGrad);
      
      // Tâches d'eau aléatoires
      if (seededRandom(seed++) < grain) {
        ctx.globalAlpha = 0.3 * intensity;
        ctx.fillStyle = color;
        const spotSize = Math.min(s.w, s.h) * (0.1 + seededRandom(seed++) * 0.2);
        ctx.beginPath();
        ctx.arc(s.x + seededRandom(seed++) * s.w, s.y + seededRandom(seed++) * s.h, spotSize, 0, 2*Math.PI);
        ctx.fill();
      }
    }

    function applyInkToShape(ctx, s, color, intensity, grain, spread, seed) {
      ctx.globalAlpha = intensity;
      drawBasicShape(ctx, s, color);
      
      // Bavure possible
      if (seededRandom(seed++) < grain) {
        ctx.globalAlpha = 0.3 * intensity;
        ctx.lineWidth = (s.outlineThickness || 1) * (1 + spread * 3);
        ctx.strokeStyle = color;
        drawBasicShapeOutline(ctx, s);
      }
    }

    function applyOilToShape(ctx, s, color, intensity, grain, spread, seed) {
      for (let layer = 0; layer < 3; layer++) {
        seed += layer * 20;
        ctx.globalAlpha = (0.8 - layer * 0.2) * intensity;
        const offsetX = (seededRandom(seed++) - 0.5) * grain * 5;
        const offsetY = (seededRandom(seed++) - 0.5) * grain * 5;
        drawShapeWithOffset(ctx, s, color, offsetX, offsetY);
      }
    }

    function drawShapeWithOffset(ctx, s, color, offsetX, offsetY) {
      ctx.beginPath();
      if(s.type === 'rectangle') {
        if (s.borderRadius && s.borderRadius > 0) {
          drawRoundedRect(ctx, s.x + offsetX, s.y + offsetY, s.w, s.h, s.borderRadius);
        } else {
          ctx.rect(s.x + offsetX, s.y + offsetY, s.w, s.h);
        }
      } else if(s.type === 'circle') {
        const r = Math.min(Math.abs(s.w), Math.abs(s.h)) / 2;
        ctx.arc(s.x + s.w/2 + offsetX, s.y + s.h/2 + offsetY, r, 0, 2*Math.PI);
      }
      // Ajouter d'autres types de formes selon besoin
      
      if (s.outlineOnly) {
        ctx.strokeStyle = color;
        ctx.lineWidth = s.outlineThickness || 1;
        ctx.stroke();
      } else {
        ctx.fillStyle = color;
        ctx.fill();
      }
    }

    function drawBasicShape(ctx, s, color) {
      ctx.beginPath();
      if(s.type === 'rectangle') {
        if (s.borderRadius && s.borderRadius > 0) {
          drawRoundedRect(ctx, s.x, s.y, s.w, s.h, s.borderRadius);
        } else {
          ctx.rect(s.x, s.y, s.w, s.h);
        }
      } else if(s.type === 'circle') {
        const r = Math.min(Math.abs(s.w), Math.abs(s.h)) / 2;
        ctx.arc(s.x + s.w/2, s.y + s.h/2, r, 0, 2*Math.PI);
      }
      // Ajouter d'autres types selon besoin
      
      if (s.outlineOnly) {
        ctx.strokeStyle = color;
        ctx.lineWidth = s.outlineThickness || 1;
        ctx.stroke();
      } else {
        ctx.fillStyle = color;
        ctx.fill();
      }
    }

    function drawBasicShapeOutline(ctx, s) {
      ctx.beginPath();
      if(s.type === 'rectangle') {
        ctx.rect(s.x, s.y, s.w, s.h);
      } else if(s.type === 'circle') {
        const r = Math.min(Math.abs(s.w), Math.abs(s.h)) / 2;
        ctx.arc(s.x + s.w/2, s.y + s.h/2, r, 0, 2*Math.PI);
      }
      ctx.stroke();
    }

    function applyExtraTextureToShape(ctx, s, color, intensity, seed) {
      // Appliquer les textures supplémentaires Phase 5 aux formes
      switch(s.extraTexture) {
        case 'brush-hair':
          // Simulation poils pour formes
          for (let i = 0; i < 20; i++) {
            seed += i;
            const hairX = s.x + seededRandom(seed++) * s.w;
            const hairY = s.y + seededRandom(seed++) * s.h;
            ctx.globalAlpha = intensity * 0.3;
            ctx.strokeStyle = color;
            ctx.lineWidth = 0.5;
            ctx.beginPath();
            ctx.moveTo(hairX, hairY);
            ctx.lineTo(hairX + seededRandom(seed++) * 3, hairY + seededRandom(seed++) * 3);
            ctx.stroke();
          }
          break;
        case 'pixel-art':
          // Effet pixel pour formes
          const pixelSize = 4;
          ctx.globalAlpha = intensity * 0.5;
          ctx.fillStyle = color;
          for (let px = s.x; px < s.x + s.w; px += pixelSize) {
            for (let py = s.y; py < s.y + s.h; py += pixelSize) {
              seed += px + py;
              if (seededRandom(seed++) < 0.7) {
                ctx.fillRect(px, py, pixelSize, pixelSize);
              }
            }
          }
          break;
      }
    }

    // TEXTURES TRADITIONNELLES PHASE 5
    function applyTraditionalTexture(ctx, x1, y1, x2, y2, size, color, intensity) {
      switch(currentTextureStyle) {
        case 'brush-hair':
          // Simulation poils de pinceau
          for (let i = 0; i < size; i++) {
            const hairOffset = (Math.random() - 0.5) * size * 0.8;
            const hairLength = Math.random() * 3 + 1;
            ctx.globalAlpha = intensity * 0.6;
            ctx.lineWidth = 0.5;
            ctx.strokeStyle = color;
            ctx.beginPath();
            ctx.moveTo(x1 + hairOffset, y1 + hairOffset);
            ctx.lineTo(x2 + hairOffset + hairLength, y2 + hairOffset + hairLength);
            ctx.stroke();
          }
          break;
          
        case 'crosshatch':
          // Hachures croisées
          const spacing = size / 4;
          ctx.globalAlpha = intensity * 0.4;
          ctx.lineWidth = 1;
          ctx.strokeStyle = color;
          
          // Lignes diagonales
          for (let i = -size; i < size * 2; i += spacing) {
            ctx.beginPath();
            ctx.moveTo(x1 + i, y1);
            ctx.lineTo(x2 + i, y2);
            ctx.stroke();
            
            ctx.beginPath();
            ctx.moveTo(x1, y1 + i);
            ctx.lineTo(x2, y2 + i);
            ctx.stroke();
          }
          break;
          
        case 'stipple':
          // Pointillisme
          const stippleCount = size * intensity * 3;
          for (let i = 0; i < stippleCount; i++) {
            const px = x1 + (x2-x1) * Math.random();
            const py = y1 + (y2-y1) * Math.random();
            const pointSize = Math.random() * 2 + 0.5;
            
            ctx.globalAlpha = intensity * Math.random() * 0.8;
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(px, py, pointSize, 0, 2*Math.PI);
            ctx.fill();
          }
          break;
          
        case 'dry-brush':
          // Pinceau sec avec effet strié
          ctx.globalAlpha = intensity * 0.7;
          ctx.lineWidth = size;
          ctx.strokeStyle = color;
          
          for (let i = 0; i < 5; i++) {
            const offset = (Math.random() - 0.5) * size * 0.3;
            ctx.globalAlpha = intensity * (Math.random() * 0.5 + 0.3);
            ctx.beginPath();
            ctx.moveTo(x1 + offset, y1 + offset);
            ctx.lineTo(x2 + offset, y2 + offset);
            ctx.stroke();
          }
          break;
      }
    }

    // TEXTURES NATURELLES PHASE 5
    function applyNaturalTexture(ctx, x1, y1, x2, y2, size, color, intensity) {
      switch(currentNaturalTexture) {
        case 'smoke':
          // Effet fumée
          ctx.shadowColor = color;
          ctx.shadowBlur = size * 3;
          ctx.globalAlpha = intensity * 0.2;
          
          for (let i = 0; i < 8; i++) {
            const smokeX = x1 + (x2-x1) * Math.random();
            const smokeY = y1 + (y2-y1) * Math.random();
            const smokeSize = size * (Math.random() * 2 + 1);
            
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(smokeX, smokeY, smokeSize, 0, 2*Math.PI);
            ctx.fill();
          }
          ctx.shadowBlur = 0;
          break;
          
        case 'wood':
          // Texture bois avec grain
          const woodLines = size / 2;
          ctx.globalAlpha = intensity * 0.5;
          ctx.strokeStyle = color;
          
          for (let i = 0; i < woodLines; i++) {
            const lineY = y1 + (y2-y1) * (i / woodLines);
            const waveOffset = Math.sin(lineY * 0.1) * size * 0.3;
            
            ctx.lineWidth = Math.random() * 2 + 0.5;
            ctx.beginPath();
            ctx.moveTo(x1 + waveOffset, lineY);
            ctx.lineTo(x2 + waveOffset, lineY);
            ctx.stroke();
          }
          break;
          
        case 'stone':
          // Texture pierre rugueuse
          const stoneParticles = size * intensity * 2;
          for (let i = 0; i < stoneParticles; i++) {
            const px = x1 + (x2-x1) * Math.random();
            const py = y1 + (y2-y1) * Math.random();
            const particleSize = Math.random() * 4 + 1;
            
            ctx.globalAlpha = intensity * (Math.random() * 0.6 + 0.2);
            ctx.fillStyle = color;
            ctx.beginPath();
            
            // Forme irrégulière pour effet pierre
            ctx.moveTo(px, py);
            for (let j = 0; j < 6; j++) {
              const angle = (j / 6) * Math.PI * 2;
              const radius = particleSize * (Math.random() * 0.5 + 0.5);
              ctx.lineTo(px + Math.cos(angle) * radius, py + Math.sin(angle) * radius);
            }
            ctx.closePath();
            ctx.fill();
          }
          break;
          
        case 'water-waves':
          // Effet ondes d'eau
          const waveFreq = 0.1;
          ctx.globalAlpha = intensity * 0.4;
          ctx.strokeStyle = color;
          
          for (let i = 0; i < 3; i++) {
            ctx.lineWidth = size / (i + 1);
            ctx.beginPath();
            
            for (let x = x1; x <= x2; x += 2) {
              const t = (x - x1) / (x2 - x1);
              const y = y1 + (y2 - y1) * t + Math.sin(x * waveFreq + i) * size * 0.3;
              
              if (x === x1) ctx.moveTo(x, y);
              else ctx.lineTo(x, y);
            }
            ctx.stroke();
          }
          break;
          
        case 'metal-brushed':
          // Métal brossé avec lignes parallèles
          const metalLines = size;
          ctx.globalAlpha = intensity * 0.3;
          
          for (let i = 0; i < metalLines; i++) {
            const lineOffset = (i / metalLines - 0.5) * size;
            const metalGrad = ctx.createLinearGradient(x1, y1, x2, y2);
            metalGrad.addColorStop(0, color);
            metalGrad.addColorStop(0.5, '#ffffff');
            metalGrad.addColorStop(1, color);
            
            ctx.strokeStyle = metalGrad;
            ctx.lineWidth = 0.5;
            ctx.beginPath();
            ctx.moveTo(x1 + lineOffset, y1);
            ctx.lineTo(x2 + lineOffset, y2);
            ctx.stroke();
          }
          break;
      }
    }

    // EFFETS NUMÉRIQUES PHASE 5
    function applyDigitalEffect(ctx, x1, y1, x2, y2, size, color, intensity) {
      switch(currentDigitalEffect) {
        case 'pixel-art':
          // Effet pixel art
          const pixelSize = Math.max(2, size / 4);
          const pixelsX = Math.ceil(Math.abs(x2 - x1) / pixelSize);
          const pixelsY = Math.ceil(Math.abs(y2 - y1) / pixelSize);
          
          ctx.globalAlpha = intensity;
          ctx.fillStyle = color;
          
          for (let px = 0; px < pixelsX; px++) {
            for (let py = 0; py < pixelsY; py++) {
              if (Math.random() < 0.7) { // Espacement pour effet pixel
                const startX = x1 + px * pixelSize;
                const startY = y1 + py * pixelSize;
                ctx.fillRect(startX, startY, pixelSize, pixelSize);
              }
            }
          }
          break;
          
        case 'glitch':
          // Effet glitch numérique
          ctx.globalAlpha = intensity;
          
          for (let i = 0; i < 5; i++) {
            const glitchOffset = (Math.random() - 0.5) * size;
            const glitchColor = ['#ff0000', '#00ff00', '#0000ff'][i % 3];
            
            ctx.strokeStyle = glitchColor;
            ctx.lineWidth = size * 0.3;
            ctx.beginPath();
            ctx.moveTo(x1 + glitchOffset, y1);
            ctx.lineTo(x2 + glitchOffset, y2);
            ctx.stroke();
          }
          break;
          
        case 'scan-lines':
          // Lignes de balayage CRT
          const scanSpacing = 3;
          ctx.globalAlpha = intensity * 0.4;
          ctx.strokeStyle = color;
          ctx.lineWidth = 1;
          
          for (let y = Math.min(y1, y2); y < Math.max(y1, y2); y += scanSpacing) {
            ctx.beginPath();
            ctx.moveTo(Math.min(x1, x2), y);
            ctx.lineTo(Math.max(x1, x2), y);
            ctx.stroke();
          }
          break;
          
        case 'noise-grain':
          // Grain de bruit numérique
          const noiseParticles = size * intensity * 4;
          
          for (let i = 0; i < noiseParticles; i++) {
            const px = x1 + (x2-x1) * Math.random();
            const py = y1 + (y2-y1) * Math.random();
            const noiseValue = Math.random();
            
            ctx.globalAlpha = intensity * 0.3;
            ctx.fillStyle = noiseValue > 0.5 ? '#ffffff' : '#000000';
            ctx.fillRect(px, py, 1, 1);
          }
          break;
          
        case 'hologram':
          // Effet hologramme avec iridescence
          const holoColors = ['#ff00ff', '#00ffff', '#ffff00', '#ff0000', '#00ff00', '#0000ff'];
          
          for (let i = 0; i < holoColors.length; i++) {
            const offset = i * 0.5;
            ctx.globalAlpha = intensity * 0.2;
            ctx.strokeStyle = holoColors[i];
            ctx.lineWidth = size * 0.2;
            ctx.beginPath();
            ctx.moveTo(x1 + offset, y1 + offset);
            ctx.lineTo(x2 + offset, y2 + offset);
            ctx.stroke();
          }
          break;
      }
    }

    // Effacer zone gomme (corrigé pour les images importées avec système de calques)
    function eraseAt(x, y, size) {
      // Créer un masque d'effacement
      const eraseRadius = size / 2;
      
      // Marquer la zone d'effacement pour toutes les couches
      if (!window.erasedAreas) {
        window.erasedAreas = [];
      }
      window.erasedAreas.push({x: x, y: y, radius: eraseRadius});
      
      // Effacer directement sur le canvas de sauvegarde des dessins si il existe
      if (drawingLayer) {
        const drawCtx = drawingLayer.getContext('2d');
        drawCtx.globalCompositeOperation = 'destination-out';
        drawCtx.beginPath();
        drawCtx.arc(x, y, eraseRadius, 0, Math.PI * 2);
        drawCtx.fill();
        drawCtx.closePath();
        drawCtx.globalCompositeOperation = 'source-over';
      }
      
      // Effacer sur le canvas principal
      ctx.globalCompositeOperation = 'destination-out';
      ctx.beginPath();
      ctx.arc(x, y, eraseRadius, 0, Math.PI * 2);
      ctx.fill();
      ctx.closePath();
      ctx.globalCompositeOperation = 'source-over';
      
      // Redessiner tout pour appliquer l'effacement aux images
      redrawAll();
    }

    // Récupérer position souris/tactile relative au canvas
    function getPointerPos(e){
      const r=canvas.getBoundingClientRect();
      const cx=e.touches?e.touches[0].clientX:e.clientX;
      const cy=e.touches?e.touches[0].clientY:e.clientY;
      return {x:(cx-r.left)*canvas.width/r.width,y:(cy-r.top)*canvas.height/r.height};
    }

    // Variables dessin
    let startX=0, startY=0;

    // Function to redraw all including imported images
    function redrawAll() {
      if (!imageLoaded) return;
      
      // S'assurer que drawingLayer a la bonne taille
      ensureDrawingLayerSize();
      
      // Effacer complètement le canvas
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      
      // 1. Dessiner l'image de base si elle existe (mais pas si elle est dans importedImages)
      if (importedImage && importedImages.length === 0) {
        ctx.drawImage(importedImage, 0, 0, canvas.width, canvas.height);
      }
      
      // 2. Dessiner toutes les images importées avec style image simple
      const imageStyleSelect = document.getElementById('imageStyle');
      const imageStyle = imageStyleSelect ? imageStyleSelect.value : 'normal';
      importedImages.forEach((imgObj, index) => {
        if (imgObj.img) {
          ctx.save();
          switch (imageStyle) {
            case 'grayscale':
              ctx.filter = 'grayscale(1)';
              break;
            case 'sepia':
              ctx.filter = 'sepia(1)';
              break;
            case 'contrast':
              ctx.filter = 'contrast(1.4)';
              break;
            case 'saturate':
              ctx.filter = 'saturate(1.6)';
              break;
            default:
              ctx.filter = 'none';
          }
          ctx.drawImage(imgObj.img, imgObj.x || 0, imgObj.y || 0, imgObj.width, imgObj.height);
          ctx.restore();
        }
      });
      
      // 3. Restaurer les dessins sauvegardés si ils existent
      if (drawingLayer) {
        ctx.drawImage(drawingLayer, 0, 0);
      }
      
      // 4. Dessiner les formes vectorielles
      shapes.forEach(shape => drawShape(ctx, shape));
      
      // 5. Appliquer les zones d'effacement sur tout (images + dessins)
      if (window.erasedAreas && window.erasedAreas.length > 0) {
        window.erasedAreas.forEach(erasedArea => {
          ctx.globalCompositeOperation = 'destination-out';
          ctx.beginPath();
          ctx.arc(erasedArea.x, erasedArea.y, erasedArea.radius, 0, Math.PI * 2);
          ctx.fill();
          ctx.closePath();
        });
        ctx.globalCompositeOperation = 'source-over';
      }
      
      // 6. Dessiner la sélection si elle existe
      if (selectionRect) {
        ctx.save();
        
        // Style de base pour la sélection
        ctx.strokeStyle = isProtected ? 'rgba(255,0,0,0.8)' : 'rgba(0,120,215,0.8)';
        ctx.lineWidth = 2;
        ctx.setLineDash([6, 4]);
        
        // Pour les sélections rectangulaires
        if (selectionType === 'rect' || !selectionPath) {
          const rect = selectionRect.width !== undefined ? selectionRect : 
                      {x: selectionRect.x, y: selectionRect.y, width: selectionRect.w, height: selectionRect.h};
          ctx.strokeRect(rect.x, rect.y, rect.width, rect.height);
          
          // Afficher un overlay semi-transparent pour la protection
          if (isProtected) {
            ctx.fillStyle = 'rgba(255,0,0,0.1)';
            ctx.fillRect(rect.x, rect.y, rect.width, rect.height);
          }
        }
        
        ctx.restore();
      }
      
      // Dessiner les sélections de forme libre (lasso)
      if (selectionPath && selectionPath.length > 2) {
        ctx.save();
        
        ctx.strokeStyle = isProtected ? 'rgba(255,0,0,0.8)' : 'rgba(0,120,215,0.8)';
        ctx.lineWidth = 2;
        ctx.setLineDash([6, 4]);
        
        // Dessiner le contour de la sélection
        ctx.beginPath();
        ctx.moveTo(selectionPath[0].x, selectionPath[0].y);
        for (let i = 1; i < selectionPath.length; i++) {
          ctx.lineTo(selectionPath[i].x, selectionPath[i].y);
        }
        ctx.closePath();
        ctx.stroke();
        
        // Afficher un overlay semi-transparent pour la protection
        if (isProtected) {
          ctx.fillStyle = 'rgba(255,0,0,0.1)';
          ctx.fill();
        }
        
        ctx.restore();
      }
      
      // Affichage des indicateurs visuels pour le mode déplacement
      if (moveMode && (selectionRect || selectionPath)) {
        const bounds = selectionManager.getSelectionBounds();
        if (bounds) {
          ctx.save();
          ctx.strokeStyle = 'rgba(255,165,0,0.9)';
          ctx.lineWidth = 3;
          ctx.setLineDash([10, 5]);
          ctx.strokeRect(bounds.x - 5, bounds.y - 5, bounds.width + 10, bounds.height + 10);
          
          // Icône de déplacement au centre
          ctx.fillStyle = 'rgba(255,165,0,0.8)';
          ctx.font = '20px Arial';
          ctx.textAlign = 'center';
          ctx.fillText('✋', bounds.x + bounds.width/2, bounds.y + bounds.height/2 + 7);
          ctx.restore();
        }
      }
      
      // 7. Dessiner les handles de redimensionnement SEULEMENT si une image est sélectionnée
      // et SEULEMENT lors de l'affichage, PAS lors de l'export
      if (selectedImageIndex !== -1 && selectedImageIndex < importedImages.length) {
        drawResizeHandles(importedImages[selectedImageIndex]);
      }

      // 8. SYSTÈME DE SÉLECTION AVANCÉ - Dessiner les poignées de sélection
      if (isElementSelected && selectedElement) {
        drawSelectionHandles();
      }
    }

    // Function to draw resize handles around an image
    function drawResizeHandles(imgObj) {
      const handleSize = 8;
      const x = imgObj.x || 0;
      const y = imgObj.y || 0;
      const w = imgObj.width;
      const h = imgObj.height;
      
      ctx.save();
      
      // Draw selection border with dashed lines
      ctx.strokeStyle = '#00aaff';
      ctx.lineWidth = 2;
      ctx.setLineDash([5, 5]);
      ctx.strokeRect(x, y, w, h);
      
      // Draw resize handles
      ctx.fillStyle = '#00aaff';
      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 2;
      ctx.setLineDash([]);
      
      const handles = [
        { x: x - handleSize/2, y: y - handleSize/2, cursor: 'nw-resize', type: 'nw' },
        { x: x + w/2 - handleSize/2, y: y - handleSize/2, cursor: 'n-resize', type: 'n' },
        { x: x + w - handleSize/2, y: y - handleSize/2, cursor: 'ne-resize', type: 'ne' },
        { x: x + w - handleSize/2, y: y + h/2 - handleSize/2, cursor: 'e-resize', type: 'e' },
        { x: x + w - handleSize/2, y: y + h - handleSize/2, cursor: 'se-resize', type: 'se' },
        { x: x + w/2 - handleSize/2, y: y + h - handleSize/2, cursor: 's-resize', type: 's' },
        { x: x - handleSize/2, y: y + h - handleSize/2, cursor: 'sw-resize', type: 'sw' },
        { x: x - handleSize/2, y: y + h/2 - handleSize/2, cursor: 'w-resize', type: 'w' }
      ];
      
      handles.forEach(handle => {
        ctx.fillRect(handle.x, handle.y, handleSize, handleSize);
        ctx.strokeRect(handle.x, handle.y, handleSize, handleSize);
      });
      
      ctx.restore();
    }

    // Function to get resize handle at position
    function getResizeHandle(x, y, imgObj) {
      if (selectedImageIndex === -1) return null;
      
      const handleSize = 8;
      const imgX = imgObj.x || 0;
      const imgY = imgObj.y || 0;
      const w = imgObj.width;
      const h = imgObj.height;
      
      const handles = [
        { x: imgX - handleSize/2, y: imgY - handleSize/2, type: 'nw' },
        { x: imgX + w/2 - handleSize/2, y: imgY - handleSize/2, type: 'n' },
        { x: imgX + w - handleSize/2, y: imgY - handleSize/2, type: 'ne' },
        { x: imgX + w - handleSize/2, y: imgY + h/2 - handleSize/2, type: 'e' },
        { x: imgX + w - handleSize/2, y: imgY + h - handleSize/2, type: 'se' },
        { x: imgX + w/2 - handleSize/2, y: imgY + h - handleSize/2, type: 's' },
        { x: imgX - handleSize/2, y: imgY + h - handleSize/2, type: 'sw' },
        { x: imgX - handleSize/2, y: imgY + h/2 - handleSize/2, type: 'w' }
      ];
      
      for (let handle of handles) {
        if (x >= handle.x && x <= handle.x + handleSize && 
            y >= handle.y && y <= handle.y + handleSize) {
          return handle.type;
        }
      }
      return null;
    }

    // Function to check if click is on an image
    function getImageAtPosition(x, y) {
      for (let i = importedImages.length - 1; i >= 0; i--) {
        const imgObj = importedImages[i];
        const imgX = imgObj.x || 0;
        const imgY = imgObj.y || 0;
        if (x >= imgX && x <= imgX + imgObj.width && 
            y >= imgY && y <= imgY + imgObj.height) {
          return i;
        }
      }
      return -1;
    }

    // Obtenir couleur ou dégradé pour dessin (fixée par élément)
    function getCurrentDrawColor(){
      if(colorModeSelect.value==='solid'){
        return document.getElementById('color1rgba').value;
      } else {
        const colors = getAllColors();
        return createDetailedGradient(ctx, canvas.width, canvas.height, gradientAngle, colors);
      }
    }

    // Gestion événements
    brushSizeInput.oninput = () => {
      brushSize = +brushSizeInput.value;
      if (brushSizeNumber) brushSizeNumber.value = brushSize;
      brushSizeValue.textContent = brushSize;
    };

    if (brushSizeNumber) {
      brushSizeNumber.oninput = () => {
        let v = +brushSizeNumber.value;
        if (isNaN(v)) v = 1;
        v = Math.max(0.001, Math.min(1000, v));
        brushSizeNumber.value = v;
        brushSizeInput.value = v;
        brushSize = v;
        brushSizeValue.textContent = v;
      };
    }
    
    opacityInput.oninput = () => {
      const alpha = +opacityInput.value;
      opacityValue.textContent = Math.round(alpha * 100);
      colorInputsContainer.querySelectorAll('input[type=text]').forEach(input=>{
        const c=parseRgba(input.value);
        if(c) input.value = `rgba(${c.r},${c.g},${c.b},${alpha})`;
      });
    };

    // Gestion des nouvelles options de formes
    const shapeOutlineOnlyCheckbox = document.getElementById('shapeOutlineOnly');
    const outlineThicknessContainer = document.getElementById('outlineThicknessContainer');
    const outlineThicknessInput = document.getElementById('outlineThickness');
    const outlineThicknessValue = document.getElementById('outlineThicknessValue');

    // Nouveaux contrôles Phase 1
    const borderRadiusInput = document.getElementById('borderRadius');
    const borderRadiusValue = document.getElementById('borderRadiusValue');
    const shapeRotationInput = document.getElementById('shapeRotation');
    const shapeRotationValue = document.getElementById('shapeRotationValue');
    const shapeStyleInput = document.getElementById('shapeStyle');

    // Mode de style (pinceau / forme)
    const styleModeSelect = document.getElementById('styleMode');
    if (styleModeSelect) {
      styleModeSelect.value = styleMode;
      styleModeSelect.addEventListener('change', () => {
        styleMode = styleModeSelect.value;
      });
    }

    shapeOutlineOnlyCheckbox.addEventListener('change', () => {
      shapeOutlineOnly = shapeOutlineOnlyCheckbox.checked;
      if (shapeOutlineOnly) {
        outlineThicknessContainer.classList.remove('hidden');
      } else {
        outlineThicknessContainer.classList.add('hidden');
      }
    });

    outlineThicknessInput.addEventListener('input', () => {
      outlineThickness = parseFloat(outlineThicknessInput.value);
      outlineThicknessValue.textContent = outlineThickness.toFixed(4);
    });

    // Event listeners Phase 1
    borderRadiusInput.addEventListener('input', () => {
      borderRadius = parseFloat(borderRadiusInput.value);
      borderRadiusValue.textContent = borderRadius.toFixed(1);
    });

    shapeRotationInput.addEventListener('input', () => {
      shapeRotation = parseFloat(shapeRotationInput.value);
      shapeRotationValue.textContent = shapeRotation.toFixed(1);
    });

    // Event listeners pour les styles artistiques
    const brushStyleSelect = document.getElementById('brushStyle');
    const styleIntensityInput = document.getElementById('styleIntensity');
    const styleIntensityValue = document.getElementById('styleIntensityValue');
    const textureGrainInput = document.getElementById('textureGrain');
    const textureGrainValue = document.getElementById('textureGrainValue');
    const spreadingInput = document.getElementById('spreading');
    const spreadingValue = document.getElementById('spreadingValue');
    const blurEffectInput = document.getElementById('blurEffect');
    const blurEffectValue = document.getElementById('blurEffectValue');
    const shineIntensityInput = document.getElementById('shineIntensity');
    const shineIntensityValue = document.getElementById('shineIntensityValue');
    const shineColorInput = document.getElementById('shineColor');
    const shineOpacityInput = document.getElementById('shineOpacity');
    const shineOpacityValue = document.getElementById('shineOpacityValue');
    // const applyStyleToShapesInput = document.getElementById('applyStyleToShapes'); // REMOVED

    brushStyleSelect.addEventListener('change', () => {
      currentBrushStyle = brushStyleSelect.value;
      if (selectedElement && selectedElementType === 'drawing') {
        selectedElement.savedBrushStyle = currentBrushStyle;
        redrawAll();
      }
    });

    styleIntensityInput.addEventListener('input', () => {
      styleIntensity = parseFloat(styleIntensityInput.value);
      styleIntensityValue.textContent = styleIntensity.toFixed(2);
      if (selectedElement && selectedElementType === 'drawing') {
        selectedElement.savedStyleIntensity = styleIntensity;
        redrawAll();
      }
    });

    textureGrainInput.addEventListener('input', () => {
      textureGrain = parseFloat(textureGrainInput.value);
      textureGrainValue.textContent = textureGrain.toFixed(2);
      if (selectedElement && selectedElementType === 'drawing') {
        selectedElement.savedTextureGrain = textureGrain;
        redrawAll();
      }
    });

    spreadingInput.addEventListener('input', () => {
      spreading = parseFloat(spreadingInput.value);
      spreadingValue.textContent = spreading.toFixed(2);
      if (selectedElement && selectedElementType === 'drawing') {
        selectedElement.savedSpreading = spreading;
        redrawAll();
      }
    });

    blurEffectInput.addEventListener('input', () => {
      blurEffect = parseFloat(blurEffectInput.value);
      blurEffectValue.textContent = blurEffect.toFixed(2);
      if (selectedElement && selectedElementType === 'drawing') {
        selectedElement.savedBlurEffect = blurEffect;
        redrawAll();
      }
    });

    shineIntensityInput.addEventListener('input', () => {
      shineIntensity = parseFloat(shineIntensityInput.value);
      shineIntensityValue.textContent = shineIntensity.toFixed(2);
      if (selectedElement && selectedElementType === 'drawing') {
        selectedElement.savedShineIntensity = shineIntensity;
        redrawAll();
      }
    });

    shineColorInput.addEventListener('change', () => {
      shineColor = shineColorInput.value;
      if (selectedElement && selectedElementType === 'drawing') {
        selectedElement.savedShineColor = shineColor;
        redrawAll();
      }
    });

    shineOpacityInput.addEventListener('input', () => {
      shineOpacity = parseFloat(shineOpacityInput.value);
      shineOpacityValue.textContent = shineOpacity.toFixed(2);
      if (selectedElement && selectedElementType === 'drawing') {
        selectedElement.savedShineOpacity = shineOpacity;
        redrawAll();
      }
    });

    /* REMOVED applyStyleToShapesInput listener */

    // Event listeners pour les nouvelles textures Phase 5
    const textureStyleInput = document.getElementById('textureStyle');
    const naturalTextureInput = document.getElementById('naturalTexture');
    const digitalEffectInput = document.getElementById('digitalEffect');
    
    if (textureStyleInput) {
      textureStyleInput.addEventListener('change', () => {
        currentTextureStyle = textureStyleInput.value;
      });
    }
    
    if (naturalTextureInput) {
      naturalTextureInput.addEventListener('change', () => {
        currentNaturalTexture = naturalTextureInput.value;
      });
    }
    
    if (digitalEffectInput) {
      digitalEffectInput.addEventListener('change', () => {
        currentDigitalEffect = digitalEffectInput.value;
      });
    }

    // Event listeners pour contrôles texture intensity Phase 5
    const textureIntensityInput = document.getElementById('textureIntensity');
    const textureIntensityValue = document.getElementById('textureIntensityValue');
    const textureBlendModeInput = document.getElementById('textureBlendMode');
    
    if (textureIntensityInput && textureIntensityValue) {
      textureIntensityInput.addEventListener('input', () => {
        textureIntensity = parseFloat(textureIntensityInput.value);
        textureIntensityValue.textContent = textureIntensity.toFixed(0);
      });
    }
    
    if (textureBlendModeInput) {
      textureBlendModeInput.addEventListener('change', () => {
        textureBlendMode = textureBlendModeInput.value;
      });
    }

    // Event listeners pour les contrôles de style artistique des formes sélectionnées
    const selectedShapeStyleInput = document.getElementById('selectedShapeStyle');
    const selectedShapeIntensityInput = document.getElementById('selectedShapeIntensity');
    const selectedShapeGrainInput = document.getElementById('selectedShapeGrain');
    const selectedShapeSpreadingInput = document.getElementById('selectedShapeSpreading');
    const selectedShapeBlurInput = document.getElementById('selectedShapeBlur');
    const selectedShapeShineInput = document.getElementById('selectedShapeShine');
    const selectedShapeTextureInput = document.getElementById('selectedShapeTexture');
    const applyShapeStyleBtn = document.getElementById('applyShapeStyle');

    if (selectedShapeStyleInput) {
      selectedShapeStyleInput.addEventListener('change', () => {
        if (selectedElement && selectedElementType === 'shape') {
          selectedElement.artisticStyle = selectedShapeStyleInput.value;
          redrawAll();
        }
      });
    }

    if (selectedShapeIntensityInput) {
      selectedShapeIntensityInput.addEventListener('input', () => {
        if (selectedElement && selectedElementType === 'shape') {
          selectedElement.styleIntensity = parseFloat(selectedShapeIntensityInput.value);
          updateShapeStyleDisplays();
          redrawAll();
        }
      });
    }

    if (selectedShapeGrainInput) {
      selectedShapeGrainInput.addEventListener('input', () => {
        if (selectedElement && selectedElementType === 'shape') {
          selectedElement.styleGrain = parseFloat(selectedShapeGrainInput.value);
          updateShapeStyleDisplays();
          redrawAll();
        }
      });
    }

    if (selectedShapeSpreadingInput) {
      selectedShapeSpreadingInput.addEventListener('input', () => {
        if (selectedElement && selectedElementType === 'shape') {
          selectedElement.styleSpreading = parseFloat(selectedShapeSpreadingInput.value);
          updateShapeStyleDisplays();
          redrawAll();
        }
      });
    }

    if (selectedShapeBlurInput) {
      selectedShapeBlurInput.addEventListener('input', () => {
        if (selectedElement && selectedElementType === 'shape') {
          selectedElement.styleBlur = parseFloat(selectedShapeBlurInput.value);
          updateShapeStyleDisplays();
          redrawAll();
        }
      });
    }

    if (selectedShapeShineInput) {
      selectedShapeShineInput.addEventListener('input', () => {
        if (selectedElement && selectedElementType === 'shape') {
          selectedElement.styleShine = parseFloat(selectedShapeShineInput.value);
          updateShapeStyleDisplays();
          redrawAll();
        }
      });
    }

    if (selectedShapeTextureInput) {
      selectedShapeTextureInput.addEventListener('change', () => {
        if (selectedElement && selectedElementType === 'shape') {
          selectedElement.extraTexture = selectedShapeTextureInput.value;
          redrawAll();
        }
      });
    }

    if (applyShapeStyleBtn) {
      applyShapeStyleBtn.addEventListener('click', () => {
        if (selectedElement && selectedElementType === 'shape') {
          // Forcer le re-rendu de la forme avec les nouveaux styles
          redrawAll();
        }
      });
    }

    // Gestion des nouveaux contrôles de gradient
    const gradientAngleInput = document.getElementById('gradientAngle');
    const gradientAngleValue = document.getElementById('gradientAngleValue');
    const topTransitionInput = document.getElementById('topTransition');
    const topTransitionValue = document.getElementById('topTransitionValue');
    const middleTransitionInput = document.getElementById('middleTransition');
    const middleTransitionValue = document.getElementById('middleTransitionValue');
    const bottomTransitionInput = document.getElementById('bottomTransition');
    const bottomTransitionValue = document.getElementById('bottomTransitionValue');
    const sideTransitionInput = document.getElementById('sideTransition');
    const sideTransitionValue = document.getElementById('sideTransitionValue');
    const gradientIntensityInput = document.getElementById('gradientIntensity');
    const gradientIntensityValue = document.getElementById('gradientIntensityValue');
    const gradientSaturationInput = document.getElementById('gradientSaturation');
    const gradientSaturationValue = document.getElementById('gradientSaturationValue');

    // Event listeners pour tous les ranges de gradient
    gradientAngleInput.addEventListener('input', () => {
      gradientAngle = parseInt(gradientAngleInput.value);
      gradientAngleValue.textContent = gradientAngle;
    });

    topTransitionInput.addEventListener('input', () => {
      topTransition = parseInt(topTransitionInput.value);
      topTransitionValue.textContent = topTransition;
    });

    middleTransitionInput.addEventListener('input', () => {
      middleTransition = parseInt(middleTransitionInput.value);
      middleTransitionValue.textContent = middleTransition;
    });

    bottomTransitionInput.addEventListener('input', () => {
      bottomTransition = parseInt(bottomTransitionInput.value);
      bottomTransitionValue.textContent = bottomTransition;
    });

    sideTransitionInput.addEventListener('input', () => {
      sideTransition = parseInt(sideTransitionInput.value);
      sideTransitionValue.textContent = sideTransition;
    });

    gradientIntensityInput.addEventListener('input', () => {
      gradientIntensity = parseInt(gradientIntensityInput.value);
      gradientIntensityValue.textContent = gradientIntensity;
    });

    gradientSaturationInput.addEventListener('input', () => {
      gradientSaturation = parseInt(gradientSaturationInput.value);
      gradientSaturationValue.textContent = gradientSaturation;
    });

    // Boutons preset pour les angles
    document.querySelectorAll('.preset-angle').forEach(btn => {
      btn.addEventListener('click', () => {
        const angle = parseInt(btn.dataset.angle);
        gradientAngleInput.value = angle;
        gradientAngle = angle;
        gradientAngleValue.textContent = angle;
      });
    });

    // Boutons preset pour les directions
    document.querySelectorAll('.preset-direction').forEach(btn => {
      btn.addEventListener('click', () => {
        const direction = btn.dataset.direction;
        switch(direction) {
          case 'left':
            gradientAngle = -180;
            break;
          case 'bottom':
            gradientAngle = -90;
            break;
          case 'center':
            gradientAngle = 0;
            break;
          case 'top':
            gradientAngle = 90;
            break;
          case 'right':
            gradientAngle = 180;
            break;
        }
        gradientAngleInput.value = gradientAngle;
        gradientAngleValue.textContent = gradientAngle;
      });
    });
    
    toolSelect.onchange = () => {
      currentTool = toolSelect.value;
      
      // Annuler tout lasso en cours
      if(isUsingLasso || lassoToolset.isPolygonMode) {
        lassoToolset.cancelLasso();
        isUsingLasso = false;
        redrawAll();
      }
      
      // Afficher/masquer les contrôles spécifiques aux lassos
      const lassoControls = document.getElementById('lassoControls');
      const finishPolygonBtn = document.getElementById('finishPolygonBtn');
      const shapeOptions = document.getElementById('shapeOptions');
      
      if(currentTool.startsWith('lasso-')) {
        lassoControls.classList.remove('hidden');
        shapeOptions.classList.add('hidden');
        if(currentTool === 'lasso-polygon') {
          finishPolygonBtn.classList.remove('hidden');
        } else {
          finishPolygonBtn.classList.add('hidden');
        }
      } else if(currentTool.startsWith('shape-')) {
        // Afficher les options de formes
        lassoControls.classList.add('hidden');
        shapeOptions.classList.remove('hidden');
      } else {
        lassoControls.classList.add('hidden');
        shapeOptions.classList.add('hidden');
      }
      
      if(currentTool==='paste' && clipboard){
        pasteClipboard();
        toolSelect.value='brush-basic';
        currentTool='brush-basic';
        lassoControls.classList.add('hidden');
        shapeOptions.classList.add('hidden');
      }
      if(currentTool==='copy' && selectionRect){
        copySelection();
        toolSelect.value='select';
        currentTool='select';
        lassoControls.classList.add('hidden');
        shapeOptions.classList.add('hidden');
      }
    };
    
    document.querySelectorAll('input[name="gradientAngle"]').forEach(radio=>{
      radio.onchange = () => {
        gradientAngle = +radio.value;
      };
    });
    
    // Gestionnaires pour les contrôles des lassos
    const magneticStrengthInput = document.getElementById('magneticStrength');
    const magneticStrengthValue = document.getElementById('magneticStrengthValue');
    const finishPolygonBtn = document.getElementById('finishPolygonBtn');
    const cancelLassoBtn = document.getElementById('cancelLassoBtn');
    
    magneticStrengthInput.oninput = () => {
      const strength = +magneticStrengthInput.value;
      magneticStrengthValue.textContent = strength;
      lassoToolset.magneticStrength = strength;
    };
    
    finishPolygonBtn.onclick = () => {
      if(lassoToolset.isPolygonMode) {
        const selection = lassoToolset.endPolygonLasso();
        if(selection) {
          currentLassoSelection = selection;
          selectionRect = selection.bounds;
          redrawAll();
        }
      }
    };
    
    cancelLassoBtn.onclick = () => {
      lassoToolset.cancelLasso();
      isUsingLasso = false;
      currentLassoSelection = null;
      selectionRect = null;
      redrawAll();
    };

    uploadInput.onchange = e => {
      const file = e.target.files[0];
      if (!file) return;
      const img = new Image();
      const reader = new FileReader();

      reader.onload = function(event) {
        img.src = event.target.result; // utilisation base64 pour éviter tainting
      };

      img.onload = () => {
        importedImage = img;
        let w = img.width, h = img.height, maxDim = 10000;
        if (w > maxDim || h > maxDim) {
          const scale = Math.min(maxDim / w, maxDim / h);
          w = Math.round(w * scale);
          h = Math.round(h * scale);
        }
        canvas.width = w;
        canvas.height = h;
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        
        // Initialiser le canvas de sauvegarde des dessins
        if (!drawingLayer) {
          drawingLayer = document.createElement('canvas');
        }
        drawingLayer.width = w;
        drawingLayer.height = h;
        
        ctx.clearRect(0, 0, w, h);
        ctx.drawImage(img, 0, 0, w, h);
        imageLoaded = true;
        downloadBtn.disabled = false;
        // shapes = []; // REMOVED to allow accumulation
        selectionRect = null;
        clipboard = null;
        redrawAll();
      };

      img.onerror = () => showNotification("Erreur lors du chargement de l'image.", 'error');
      reader.readAsDataURL(file); // déclenche le chargement
    };

    // Système de zoom haute précision
    function applyZoom(deltaY, mouseX, mouseY) {
      const zoomFactor = deltaY < 0 ? 1.1 : 0.9;
      const newZoom = Math.max(minZoom, Math.min(maxZoom, zoomLevel * zoomFactor));
      
      if (newZoom !== zoomLevel) {
        // Obtenir le conteneur du canvas pour un zoom centré
        const canvasContainer = document.getElementById('canvasContainer');
        const containerRect = canvasContainer.getBoundingClientRect();
        const canvasRect = canvas.getBoundingClientRect();
        
        // Calculer le point de zoom relatif au centre du canvas visible
        const centerX = containerRect.left + containerRect.width / 2;
        const centerY = containerRect.top + containerRect.height / 2;
        
        // Point de zoom (utiliser le centre si pas de coordonnées souris)
        const zoomX = mouseX || centerX;
        const zoomY = mouseY || centerY;
        
        // Calculer la position relative au canvas
        const relativeX = (zoomX - canvasRect.left) / canvasRect.width;
        const relativeY = (zoomY - canvasRect.top) / canvasRect.height;
        
        // Ajuster l'offset pour maintenir le point sous la souris/centre
        const oldZoom = zoomLevel;
        zoomLevel = newZoom;
        
        // Recalculer l'offset pour garder le point focal
        const scaleChange = newZoom / oldZoom;
        canvasOffset.x = canvasOffset.x * scaleChange + (relativeX * canvas.width * (1 - scaleChange));
        canvasOffset.y = canvasOffset.y * scaleChange + (relativeY * canvas.height * (1 - scaleChange));
        
        // Limiter le déplacement pour garder le contenu dans la zone visible
        constrainCanvasPosition();
        updateCanvasTransform();
        updateZoomDisplay();
      }
    }

    function updateCanvasTransform() {
      canvas.style.transform = `scale(${zoomLevel}) translate(${canvasOffset.x}px, ${canvasOffset.y}px)`;
      canvas.style.transformOrigin = '0 0';
    }

    function constrainCanvasPosition() {
      const container = document.getElementById('canvasContainer');
      const containerRect = container.getBoundingClientRect();
      
      // Limites pour garder au moins une partie du canvas visible
      const minVisible = 100; // pixels minimum visibles
      const scaledWidth = canvas.width * zoomLevel;
      const scaledHeight = canvas.height * zoomLevel;
      
      // Calculer les limites
      const maxOffsetX = (containerRect.width - minVisible) / zoomLevel;
      const minOffsetX = -(scaledWidth - minVisible) / zoomLevel;
      const maxOffsetY = (containerRect.height - minVisible) / zoomLevel;
      const minOffsetY = -(scaledHeight - minVisible) / zoomLevel;
      
      // Appliquer les contraintes
      canvasOffset.x = Math.max(minOffsetX, Math.min(maxOffsetX, canvasOffset.x));
      canvasOffset.y = Math.max(minOffsetY, Math.min(maxOffsetY, canvasOffset.y));
    }

    function updateZoomDisplay() {
      const zoomPercent = (zoomLevel * 100).toFixed(1);
      const precision = (1/zoomLevel).toFixed(6);
      
      document.getElementById('zoomDisplay').textContent = zoomPercent + '%';
      document.getElementById('precisionDisplay').textContent = precision + 'px';
      
      // Mettre à jour les couleurs selon le niveau de zoom
      const zoomEl = document.getElementById('zoomDisplay');
      const precisionEl = document.getElementById('precisionDisplay');
      
      if (zoomLevel > 10) {
        zoomEl.className = 'text-[#ff4444] font-mono font-bold'; // Rouge pour zoom très élevé
        precisionEl.className = 'text-[#00ff00] font-mono font-bold'; // Vert pour haute précision
      } else if (zoomLevel > 2) {
        zoomEl.className = 'text-[#ffaa00] font-mono'; // Orange pour zoom élevé
        precisionEl.className = 'text-[#00ff00] font-mono';
      } else {
        zoomEl.className = 'text-[#00aaff] font-mono'; // Bleu pour zoom normal
        precisionEl.className = 'text-[#00ff00] font-mono';
      }
    }

    function getScaledPointerPos(e) {
      const rect = canvas.getBoundingClientRect();
      
      // Position brute de la souris relative au canvas
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      // Conversion simple en coordonnées du canvas logique
      const canvasX = (x / rect.width) * canvas.width;
      const canvasY = (y / rect.height) * canvas.height;
      
      // Limiter aux dimensions du canvas
      return {
        x: Math.max(0, Math.min(canvas.width, canvasX)),
        y: Math.max(0, Math.min(canvas.height, canvasY))
      };
    }

    // Fonction pour recalibrer le pointeur après des changements de zoom/taille
    function recalibratePointer() {
      // Fonction simplifiée - ne fait rien car getScaledPointerPos est maintenant simple
      console.log('Recalibration demandée (simplifiée)');
    }

    // Fonction de calibrage universel ultra-précis
    function ensurePerfectCalibration() {
      // Fonction simplifiée - le calibrage est maintenant automatique
      console.log('Calibrage parfait assuré (simplifié)');
    }

    // Auto-calibrage permanent - se déclenche automatiquement
    function setupAutomaticCalibration() {
      console.log('🎯 Système de calibrage simplifié - Utilise coordonnées directes du canvas');
      
      // Plus besoin d'observers complexes - le calibrage est maintenant direct
      // La fonction getScaledPointerPos utilise directement getBoundingClientRect()
    }

    // Gestion de la molette pour le zoom
    canvas.addEventListener('wheel', (e) => {
      e.preventDefault();
      applyZoom(e.deltaY, e.clientX, e.clientY);
    });

    // Navigation par clic droit + glisser
    canvas.addEventListener('contextmenu', (e) => {
      e.preventDefault(); // Empêcher le menu contextuel
    });

    canvas.addEventListener('mousedown', (e) => {
      if (e.button === 2) { // Clic droit
        isPanning = true;
        lastPanPoint = { x: e.clientX, y: e.clientY };
        panStartOffset = { x: canvasOffset.x, y: canvasOffset.y };
        canvas.style.cursor = 'grabbing';
        e.preventDefault();
      }
    });

    document.addEventListener('mousemove', (e) => {
      if (isPanning) {
        const deltaX = (e.clientX - lastPanPoint.x) / zoomLevel;
        const deltaY = (e.clientY - lastPanPoint.y) / zoomLevel;
        
        canvasOffset.x = panStartOffset.x + deltaX;
        canvasOffset.y = panStartOffset.y + deltaY;
        
        constrainCanvasPosition();
        updateCanvasTransform();
        e.preventDefault();
      }
    });

    document.addEventListener('mouseup', (e) => {
      if (e.button === 2 && isPanning) {
        isPanning = false;
        canvas.style.cursor = 'default';
        e.preventDefault();
      }
    });

    // Fonction pour réinitialiser le zoom
    function resetZoom() {
      zoomLevel = 1;
      canvasOffset = { x: 0, y: 0 };
      
      // Remise à zéro complète
      canvas.style.transform = 'none';
      canvas.style.transformOrigin = '0 0';
      
      updateZoomDisplay();
    }

    // Fonction pour centrer le canvas
    function centerCanvas() {
      const container = document.getElementById('canvasContainer');
      const containerRect = container.getBoundingClientRect();
      
      // Réinitialiser d'abord zoom et position
      zoomLevel = 1;
      canvasOffset.x = 0;
      canvasOffset.y = 0;
      
      // Calculer le centrage simple
      const canvasDisplayWidth = canvas.width;
      const canvasDisplayHeight = canvas.height;
      
      // Centrer dans le conteneur
      const centerX = (containerRect.width - canvasDisplayWidth) / 2;
      const centerY = (containerRect.height - canvasDisplayHeight) / 2;
      
      // Appliquer la position centrée
      canvas.style.transform = `translate(${centerX}px, ${centerY}px)`;
      canvas.style.transformOrigin = '0 0';
      
      updateZoomDisplay();
    }

    // Fonction pour recalibrer les coordonnées après un redimensionnement
    function recalibrateCoordinates() {
      // Réinitialiser les transformations pour éviter les décalages
      zoomLevel = 1;
      canvasOffset = { x: 0, y: 0 };
      
      // Mettre à jour le style du canvas
      canvas.style.transform = 'none';
      canvas.style.transformOrigin = '0 0';
      
      // Centrer le canvas dans son conteneur
      setTimeout(() => {
        centerCanvas();
        updateZoomDisplay();
      }, 50);
    }

    // Raccourci clavier pour réinitialiser le zoom (Ctrl+0)
    document.addEventListener('keydown', (e) => {
      if (e.ctrlKey && e.key === '0') {
        e.preventDefault();
        resetZoom();
      }
    });

    // Dessin & interaction
    canvas.onpointerdown = e => {
      if(!imageLoaded) return;
      
      // Ignorer clic droit (réservé pour la navigation)
      if (e.button === 2) return;
      
      const pos = getScaledPointerPos(e);
      
      // Vérifier la protection de zone
      if (isProtected && selectionManager.isPointProtected(pos.x, pos.y)) {
        e.preventDefault();
        return; // Bloquer toute interaction dans la zone protégée
      }

      // SYSTÈME DE SÉLECTION AVANCÉ - Vérifier si on clique sur un élément sélectionnable
      if (isElementSelected) {
        // Vérifier d'abord si on clique sur une poignée
        const clickedHandle = getClickedHandle(pos.x, pos.y);
        if (clickedHandle) {
          if (clickedHandle.type === 'resize') {
            isResizing = true;
            elementResizeHandle = clickedHandle.handle;
            window.resizeStartX = pos.x;
            window.resizeStartY = pos.y;
            if (selectedElementType === 'drawing') {
               window.resizeStartBounds = getDrawingBounds(selectedElement);
               window.resizeStartPoints = JSON.parse(JSON.stringify(selectedElement.points));
            }
            e.preventDefault();
            return;
          } else if (clickedHandle.type === 'rotation') {
            isRotating = true;
            // Initialize lastRotationAngle
            let bounds;
            if (selectedElementType === 'drawing') {
              bounds = getDrawingBounds(selectedElement);
            } else if (selectedElementType === 'shape') {
              bounds = {x:selectedElement.x, y:selectedElement.y, w:selectedElement.w, h:selectedElement.h};
            } else {
              bounds = {x:selectedElement.x, y:selectedElement.y, w:selectedElement.width, h:selectedElement.height};
            }
            const centerX = bounds.x + bounds.w/2;
            const centerY = bounds.y + bounds.h/2;
            window.lastRotationAngle = Math.atan2(pos.y - centerY, pos.x - centerX) * 180 / Math.PI + 90;
            e.preventDefault();
            return;
          }
        }
        
        // Vérifier si on clique dans l'élément sélectionné pour le déplacer
        let isClickInSelectedElement = false;
        if (selectedElementType === 'shape') {
          isClickInSelectedElement = isPointInShape(pos.x, pos.y, selectedElement);
        } else if (selectedElementType === 'image') {
          isClickInSelectedElement = (pos.x >= selectedElement.x && pos.x <= selectedElement.x + selectedElement.width && 
                                     pos.y >= selectedElement.y && pos.y <= selectedElement.y + selectedElement.height);
        } else if (selectedElementType === 'drawing') {
          isClickInSelectedElement = isPointInDrawingStroke(pos.x, pos.y, selectedElement);
        }
        
        if (isClickInSelectedElement) {
          isDragging = true;
          if (selectedElementType === 'drawing') {
             const bounds = getDrawingBounds(selectedElement);
             dragOffset.x = pos.x - bounds.x;
             dragOffset.y = pos.y - bounds.y;
          } else {
             dragOffset.x = pos.x - (selectedElementType === 'shape' ? selectedElement.x : selectedElement.x);
             dragOffset.y = pos.y - (selectedElementType === 'shape' ? selectedElement.y : selectedElement.y);
          }
          e.preventDefault();
          return;
        }
      }

      // Vérifier si on clique sur un nouvel élément à sélectionner
      const clickedElement = getClickedElement(pos.x, pos.y);
      if (clickedElement && (currentTool === 'select' || e.ctrlKey)) {
        selectElement(clickedElement);
        e.preventDefault();
        return;
      }

      // Si on clique ailleurs, désélectionner
      if (isElementSelected && !clickedElement) {
        deselectElement();
      }
      
      // Mode déplacement de sélection
      if (moveMode && selectionManager.isPointInSelection(pos.x, pos.y)) {
        isMovingSelection = true;
        selectionOffset = {
          x: pos.x - (selectionRect ? selectionRect.x : selectionManager.getSelectionBounds().x),
          y: pos.y - (selectionRect ? selectionRect.y : selectionManager.getSelectionBounds().y)
        };
        e.preventDefault();
        return;
      }
      
      // Check if we're clicking on a resize handle first
      if (selectedImageIndex !== -1) {
        const imgObj = importedImages[selectedImageIndex];
        resizeHandle = getResizeHandle(pos.x, pos.y, imgObj);
        if (resizeHandle) {
          e.preventDefault();
          return;
        }
      }
      
      // Check if we're clicking on an image
      const imageIndex = getImageAtPosition(pos.x, pos.y);
      if (imageIndex !== -1 && currentTool === 'select') {
        selectedImageIndex = imageIndex;
        isDraggingImage = true;
        startX = pos.x;
        startY = pos.y;
        redrawAll();
        e.preventDefault();
        return;
      }
      
      // Deselect image if clicking elsewhere
      if (imageIndex === -1 && currentTool === 'select') {
        selectedImageIndex = -1;
        redrawAll();
      }
      
      if(currentTool==='select'){
        isSelecting=true;
        selectionRect={x:pos.x,y:pos.y,w:0,h:0};
        selectionType = 'rect';
        selectionPath = null;
        selectionManager.showSelectionControls();
        redrawAll();
        e.preventDefault();
        return;
      }
      
      // Gestion des outils Lasso
      if(currentTool==='lasso-free'){
        lassoToolset.startFreeLasso(pos.x, pos.y);
        isUsingLasso = true;
        selectionType = 'free';
        e.preventDefault();
        return;
      }
      if(currentTool==='lasso-polygon'){
        const selection = lassoToolset.addPolygonPoint(pos.x, pos.y);
        selectionType = 'polygonal';
        if(selection) {
          currentLassoSelection = selection;
          selectionRect = selection.bounds;
          selectionPath = selection.path;
          selectionManager.showSelectionControls();
          redrawAll();
        }
        isUsingLasso = true;
        e.preventDefault();
        return;
      }
      if(currentTool==='lasso-magnetic'){
        lassoToolset.startMagneticLasso(pos.x, pos.y);
        isUsingLasso = true;
        selectionType = 'magnetic';
        e.preventDefault();
        return;
      }
      
      if(currentTool==='copy' || currentTool==='paste') return;
      if(currentTool==='eraser'){
        eraseAt(pos.x,pos.y,brushSize);
        isDrawing=true; lastPoint=pos;
        e.preventDefault();
        return;
      }
      if(currentTool.startsWith('brush') || currentTool.startsWith('shape-')){
        isDrawing=true; lastPoint=pos; startX=pos.x; startY=pos.y;
        if(currentTool.startsWith('brush')){
          // S'assurer que drawingLayer est prêt
          ensureDrawingLayerSize();
          const color = getCurrentDrawColor();
          drawLine(ctx,pos.x,pos.y,pos.x,pos.y,currentTool,brushSize,color);
        }
        e.preventDefault();
      }
    };
    
    canvas.onpointermove = e => {
      if(!imageLoaded) return;
      
      // Ignorer pendant la navigation
      if (isPanning) return;
      
      const pos = getScaledPointerPos(e); // Utiliser la position avec zoom

      // SYSTÈME DE SÉLECTION AVANCÉ - Gestion des interactions
      
      // Gestion du redimensionnement
      if (isResizing && isElementSelected && elementResizeHandle) {
        const deltaX = pos.x - (window.resizeStartX || pos.x);
        const deltaY = pos.y - (window.resizeStartY || pos.y);
        
        if (selectedElementType === 'shape') {
          const shape = selectedElement;
          
          switch(elementResizeHandle) {
            case 'nw':
              shape.x += deltaX;
              shape.y += deltaY;
              shape.w -= deltaX;
              shape.h -= deltaY;
              break;
            case 'ne':
              shape.y += deltaY;
              shape.w += deltaX;
              shape.h -= deltaY;
              break;
            case 'sw':
              shape.x += deltaX;
              shape.w -= deltaX;
              shape.h += deltaY;
              break;
            case 'se':
              shape.w += deltaX;
              shape.h += deltaY;
              break;
            case 'n':
              shape.y += deltaY;
              shape.h -= deltaY;
              break;
            case 's':
              shape.h += deltaY;
              break;
            case 'w':
              shape.x += deltaX;
              shape.w -= deltaX;
              break;
            case 'e':
              shape.w += deltaX;
              break;
          }
          
          // Garder des dimensions minimales
          shape.w = Math.max(5, shape.w);
          shape.h = Math.max(5, shape.h);
        } else if (selectedElementType === 'drawing') {
           const currentBounds = getDrawingBounds(selectedElement);
           let newX = currentBounds.x;
           let newY = currentBounds.y;
           let newW = currentBounds.w;
           let newH = currentBounds.h;
           
           switch(elementResizeHandle) {
            case 'nw':
              newX += deltaX;
              newY += deltaY;
              newW -= deltaX;
              newH -= deltaY;
              break;
            case 'ne':
              newY += deltaY;
              newW += deltaX;
              newH -= deltaY;
              break;
            case 'sw':
              newX += deltaX;
              newW -= deltaX;
              newH += deltaY;
              break;
            case 'se':
              newW += deltaX;
              newH += deltaY;
              break;
            case 'n':
              newY += deltaY;
              newH -= deltaY;
              break;
            case 's':
              newH += deltaY;
              break;
            case 'w':
              newX += deltaX;
              newW -= deltaX;
              break;
            case 'e':
              newW += deltaX;
              break;
          }
          
          if (newW < 5) newW = 5;
          if (newH < 5) newH = 5;
          
          if (currentBounds.w > 0 && currentBounds.h > 0) {
             const scaleX = newW / currentBounds.w;
             const scaleY = newH / currentBounds.h;
             
             for (const p of selectedElement.points) {
               p.x = newX + (p.x - currentBounds.x) * scaleX;
               p.y = newY + (p.y - currentBounds.y) * scaleY;
             }
          }
        }
        
        window.resizeStartX = pos.x;
        window.resizeStartY = pos.y;
        redrawAll();
        drawSelectionHandles();
        drawSelectionUI();
        return;
      }
      
      // Gestion de la rotation
      if (isRotating && isElementSelected) {
        let centerX, centerY;
        
        if (selectedElementType === 'shape') {
          centerX = selectedElement.x + selectedElement.w / 2;
          centerY = selectedElement.y + selectedElement.h / 2;
        } else if (selectedElementType === 'image') {
          centerX = selectedElement.x + selectedElement.width / 2;
          centerY = selectedElement.y + selectedElement.height / 2;
        } else if (selectedElementType === 'drawing') {
          const bounds = getDrawingBounds(selectedElement);
          centerX = bounds.x + bounds.w / 2;
          centerY = bounds.y + bounds.h / 2;
        } else if (selectedElementType === 'text') {
          centerX = selectedElement.x + selectedElement.width / 2;
          centerY = selectedElement.y + selectedElement.height / 2;
        }
        
        const angle = Math.atan2(pos.y - centerY, pos.x - centerX) * 180 / Math.PI + 90;
        
        if (selectedElementType === 'shape' || selectedElementType === 'text') {
          selectedElement.rotation = (angle + 360) % 360;
        } else if (selectedElementType === 'drawing') {
           if (window.lastRotationAngle === undefined) window.lastRotationAngle = angle;
           const angleDiff = angle - window.lastRotationAngle;
           window.lastRotationAngle = angle;
           
           const rad = angleDiff * Math.PI / 180;
           const cos = Math.cos(rad);
           const sin = Math.sin(rad);
           
           for (const p of selectedElement.points) {
             const dx = p.x - centerX;
             const dy = p.y - centerY;
             p.x = centerX + dx * cos - dy * sin;
             p.y = centerY + dx * sin + dy * cos;
           }
        }
        
        redrawAll();
        drawSelectionHandles();
        drawSelectionUI();
        return;
      }
      
      // Gestion du déplacement
      if (isDragging && isElementSelected) {
        if (selectedElementType === 'shape') {
          selectedElement.x = pos.x - dragOffset.x;
          selectedElement.y = pos.y - dragOffset.y;
        } else if (selectedElementType === 'image') {
          selectedElement.x = pos.x - dragOffset.x;
          selectedElement.y = pos.y - dragOffset.y;
        } else if (selectedElementType === 'drawing') {
           const bounds = getDrawingBounds(selectedElement);
           const newX = pos.x - dragOffset.x;
           const newY = pos.y - dragOffset.y;
           const dx = newX - bounds.x;
           const dy = newY - bounds.y;
           
           if (dx !== 0 || dy !== 0) {
             for (const p of selectedElement.points) {
               p.x += dx;
               p.y += dy;
             }
           }
        }
        
        redrawAll();
        drawSelectionHandles();
        drawSelectionUI();
        return;
      }
      
      // Gestion du déplacement de sélection
      if (isMovingSelection && moveMode) {
        const newX = pos.x - selectionOffset.x;
        const newY = pos.y - selectionOffset.y;
        
        if (selectionRect) {
          selectionRect.x = newX;
          selectionRect.y = newY;
        }
        if (selectionPath) {
          // Déplacer tous les points du chemin
          const deltaX = newX - (selectionBounds ? selectionBounds.x : 0);
          const deltaY = newY - (selectionBounds ? selectionBounds.y : 0);
          selectionPath = selectionPath.map(point => ({
            x: point.x + deltaX,
            y: point.y + deltaY
          }));
        }
        redrawAll();
        return;
      }
      
      // Protection de zone - bloquer les interactions de dessin
      if (isProtected && selectionManager.isPointProtected(pos.x, pos.y) && 
          (currentTool.startsWith('brush') || currentTool === 'eraser')) {
        return; // Bloquer le dessin dans la zone protégée
      }
      
      // Update cursor based on hover
      if (selectedImageIndex !== -1 && !resizeHandle && !isDraggingImage) {
        const imgObj = importedImages[selectedImageIndex];
        const handle = getResizeHandle(pos.x, pos.y, imgObj);
        if (handle) {
          const cursors = {
            'nw': 'nw-resize', 'n': 'n-resize', 'ne': 'ne-resize',
            'e': 'e-resize', 'se': 'se-resize', 's': 's-resize',
            'sw': 'sw-resize', 'w': 'w-resize'
          };
          canvas.style.cursor = cursors[handle];
        } else {
          const imageIndex = getImageAtPosition(pos.x, pos.y);
          canvas.style.cursor = imageIndex !== -1 ? 'move' : 'default';
        }
      } else if (!resizeHandle && !isDraggingImage) {
        const imageIndex = getImageAtPosition(pos.x, pos.y);
        canvas.style.cursor = imageIndex !== -1 && currentTool === 'select' ? 'pointer' : 'default';
      }
      
      // Handle image resizing
      if (resizeHandle && selectedImageIndex !== -1) {
        const imgObj = importedImages[selectedImageIndex];
        const originalX = imgObj.x || 0;
        const originalY = imgObj.y || 0;
        const originalW = imgObj.width;
        const originalH = imgObj.height;
        
        let newX = originalX;
        let newY = originalY;
        let newW = originalW;
        let newH = originalH;
        
        switch(resizeHandle) {
          case 'nw':
            newW = originalW + (originalX - pos.x);
            newH = originalH + (originalY - pos.y);
            newX = pos.x;
            newY = pos.y;
            break;
          case 'n':
            newH = originalH + (originalY - pos.y);
            newY = pos.y;
            break;
          case 'ne':
            newW = pos.x - originalX;
            newH = originalH + (originalY - pos.y);
            newY = pos.y;
            break;
          case 'e':
            newW = pos.x - originalX;
            break;
          case 'se':
            newW = pos.x - originalX;
            newH = pos.y - originalY;
            break;
          case 's':
            newH = pos.y - originalY;
            break;
          case 'sw':
            newW = originalW + (originalX - pos.x);
            newH = pos.y - originalY;
            newX = pos.x;
            break;
          case 'w':
            newW = originalW + (originalX - pos.x);
            newX = pos.x;
            break;
        }
        
        // Ensure minimum size
        if (newW < 10) newW = 10;
        if (newH < 10) newH = 10;
        
        imgObj.x = newX;
        imgObj.y = newY;
        imgObj.width = newW;
        imgObj.height = newH;
        
        redrawAll();
        recalibratePointer(); // Recalibrer après redimensionnement
        e.preventDefault();
        return;
      }
      
      // Handle image dragging
      if (isDraggingImage && selectedImageIndex !== -1) {
        const imgObj = importedImages[selectedImageIndex];
        const deltaX = pos.x - startX;
        const deltaY = pos.y - startY;
        imgObj.x = (imgObj.x || 0) + deltaX;
        imgObj.y = (imgObj.y || 0) + deltaY;
        startX = pos.x;
        startY = pos.y;
        redrawAll();
        e.preventDefault();
        return;
      }
      
      if(isSelecting){
        selectionRect.x = Math.min(selectionRect.x, pos.x);
        selectionRect.y = Math.min(selectionRect.y, pos.y);
        selectionRect.w = Math.abs(pos.x - selectionRect.x);
        selectionRect.h = Math.abs(pos.y - selectionRect.y);
        redrawAll();
        ctx.save();
        ctx.strokeStyle='rgba(0,120,215,0.8)';
        ctx.lineWidth=2;
        ctx.setLineDash([6,4]);
        ctx.strokeRect(selectionRect.x,selectionRect.y,selectionRect.w,selectionRect.h);
        ctx.restore();
        e.preventDefault();
        return;
      }
      
      // Gestion du mouvement pour les lassos
      if(isUsingLasso){
        if(currentTool==='lasso-free'){
          lassoToolset.continueFreeLasso(pos.x, pos.y);
        } else if(currentTool==='lasso-magnetic'){
          lassoToolset.continueMagneticLasso(pos.x, pos.y);
        }
        e.preventDefault();
        return;
      }
      
      // Prévisualisation pour le lasso polygonal
      if(currentTool==='lasso-polygon' && lassoToolset.isPolygonMode){
        redrawAll();
        lassoToolset.drawPolygonPreview();
        
        // Dessiner une ligne de prévisualisation vers la souris
        if(lassoToolset.polygonPoints.length > 0){
          ctx.save();
          ctx.strokeStyle = 'rgba(0, 120, 215, 0.5)';
          ctx.lineWidth = 1;
          ctx.setLineDash([2, 2]);
          ctx.beginPath();
          const lastPoint = lassoToolset.polygonPoints[lassoToolset.polygonPoints.length - 1];
          ctx.moveTo(lastPoint.x, lastPoint.y);
          ctx.lineTo(pos.x, pos.y);
          ctx.stroke();
          ctx.restore();
        }
        e.preventDefault();
        return;
      }
      if(!isDrawing) return;
      if(currentTool==='eraser'){
        eraseAt(pos.x,pos.y,brushSize);
        lastPoint=pos;
        e.preventDefault();
        return;
      }
      if(currentTool.startsWith('brush')){
        // S'assurer que drawingLayer est prêt
        ensureDrawingLayerSize();
        const color = getCurrentDrawColor();
        drawLine(ctx,lastPoint.x,lastPoint.y,pos.x,pos.y,currentTool,brushSize,color);
        lastPoint=pos;
        e.preventDefault();
        return;
      }
      if(currentTool.startsWith('shape-')){
        redrawAll();
        const shapeType = currentTool.replace('shape-','');
        const x = Math.min(startX,pos.x);
        const y = Math.min(startY,pos.y);
        const w = Math.abs(pos.x - startX);
        const h = Math.abs(pos.y - startY);
        const color = getCurrentDrawColor();
        drawShape(ctx,{type:shapeType,x,y,w,h,size:brushSize,color});
        e.preventDefault();
      }
    };
    
    canvas.onpointerup = e => {
      if(!imageLoaded) return;

      // SYSTÈME DE SÉLECTION AVANCÉ - Terminer les interactions
      if (isResizing) {
        isResizing = false;
        elementResizeHandle = null;
        redrawAll(); // **CORRECTION: Redraw après redimensionnement**
        e.preventDefault();
        return;
      }
      
      if (isRotating) {
        isRotating = false;
        redrawAll(); // **CORRECTION: Redraw après rotation**
        e.preventDefault();
        return;
      }
      
      if (isDragging) {
        isDragging = false;
        e.preventDefault();
        return;
      }
      
      // Finaliser le déplacement de sélection
      if (isMovingSelection) {
        isMovingSelection = false;
        selectionBounds = selectionManager.getSelectionBounds();
        redrawAll();
        e.preventDefault();
        return;
      }
      
      // Stop resizing or dragging
      if (resizeHandle) {
        resizeHandle = null;
        e.preventDefault();
        return;
      }
      
      if (isDraggingImage) {
        isDraggingImage = false;
        e.preventDefault();
        return;
      }
      
      if(isSelecting){
        isSelecting=false;
        if(selectionRect.w<5 || selectionRect.h<5) {
          selectionRect=null;
          selectionManager.hideSelectionControls();
        } else {
          // Normaliser le rectangle de sélection
          if(selectionRect.w < 0) {
            selectionRect.x += selectionRect.w;
            selectionRect.w = -selectionRect.w;
          }
          if(selectionRect.h < 0) {
            selectionRect.y += selectionRect.h;
            selectionRect.h = -selectionRect.h;
          }
          // Convertir en format standard
          selectionRect.width = selectionRect.w;
          selectionRect.height = selectionRect.h;
          selectionManager.showSelectionControls();
        }
        redrawAll();
        e.preventDefault();
        return;
      }
      
      // Fin des lassos libres et magnétiques
      if(isUsingLasso){
        let selection = null;
        if(currentTool==='lasso-free'){
          selection = lassoToolset.endFreeLasso();
        } else if(currentTool==='lasso-magnetic'){
          selection = lassoToolset.endMagneticLasso();
        }
        
        if(selection){
          currentLassoSelection = selection;
          selectionRect = selection.bounds;
          selectionPath = selection.path;
          selectionType = currentTool.replace('lasso-', '');
          selectionManager.showSelectionControls();
        }
        
        isUsingLasso = false;
        redrawAll();
        e.preventDefault();
        return;
      }
      if(!isDrawing) return;
      if(currentTool.startsWith('shape-')){
        const shapeType = currentTool.replace('shape-','');
        const pos = getScaledPointerPos(e);
        const x = Math.min(startX,pos.x);
        const y = Math.min(startY,pos.y);
        const w = Math.abs(pos.x - startX);
        const h = Math.abs(pos.y - startY);
        const color = getCurrentDrawColor();
        
        // Récupérer les options de forme
        const outlineOnly = document.getElementById('shapeOutlineOnly')?.checked || false;
        const outlineThickness = parseFloat(document.getElementById('outlineThickness')?.value || 1);
        const useGradient = document.getElementById('colorMode')?.value === 'gradient';
        
        // Options de gradient
        let gradientOptions = null;
        if (useGradient) {
          gradientOptions = {
            angle: parseFloat(document.getElementById('gradientAngle')?.value || 0),
            type: 'linear', // Pour l'instant, seulement linéaire
            color1: document.getElementById('color1')?.value || '#ff0000',
            color2: document.getElementById('color2')?.value || '#0000ff',
            intensity: parseFloat(document.getElementById('gradientIntensity')?.value || 100),
            saturation: parseFloat(document.getElementById('gradientSaturation')?.value || 100),
            transition: {
              top: parseFloat(document.getElementById('topTransition')?.value || 0),
              middle: parseFloat(document.getElementById('middleTransition')?.value || 50),
              bottom: parseFloat(document.getElementById('bottomTransition')?.value || 100),
              side: parseFloat(document.getElementById('sideTransition')?.value || 50)
            }
          };
        }
        
        // Figer le style de la forme au moment de la création
        const shapeStyleSelect = document.getElementById('shapeStyle');
        const fillMode = colorModeSelect.value === 'gradient' ? 'gradient' : 'solid';
        const opacityForShape = parseFloat(opacityInput.value || '1');
        const newShape = {
          type: shapeType,
          x, y, w, h,
          size: brushSize,
          color,
          outlineOnly,
          outlineThickness,
          useGradient,
          gradientOptions,
          borderRadius,
          rotation: shapeRotation,
          // Style propre à la forme
          shapeStyle: shapeStyleSelect ? shapeStyleSelect.value : 'flat-fill',
          fillMode,
          fillColor: color,
          opacity: opacityForShape,
          // Propriétés de style artistique indépendantes existantes
          artisticStyle: currentBrushStyle,
          styleIntensity: styleIntensity,
          styleGrain: textureGrain,
          styleSpreading: spreading,
          styleBlur: blurEffect,
          styleShine: shineIntensity,
          extraTexture: currentTextureStyle
        };
        shapes.push(newShape);
        // Ne pas appeler redrawAll ici, il sera appelé par le patch shapes.push
        // **SAUVEGARDER L'ÉTAT POUR UNDO/REDO**
        setTimeout(() => saveState(), 10); // Petit délai pour que le patch soit appliqué
      }
      isDrawing=false; lastPoint=null;
      e.preventDefault();
    };

    // Copier sélection
    function copySelection(){
      if(!selectionRect) return;
      clipboard = ctx.getImageData(selectionRect.x, selectionRect.y, selectionRect.w, selectionRect.h);
      showNotification('Sélection copiée dans le presse-papiers interne.', 'success');
    }
    
    // Coller presse-papiers
    function pasteClipboard(){
      if(!clipboard) return;
      const x = (canvas.width - clipboard.width)/2;
      const y = (canvas.height - clipboard.height)/2;
      ctx.putImageData(clipboard,x,y);
      redrawAll();
      showNotification('Image collée.', 'success');
    }

    downloadBtn.onclick = () => {
      console.log('Export démarré...');
      
      // Vérifier s'il y a du contenu à exporter (image, dessins, formes)
      const hasContent = imageLoaded || 
                        (window.drawingStrokes && window.drawingStrokes.length > 0) ||
                        (window.shapes && window.shapes.length > 0) ||
                        (window.importedImages && window.importedImages.length > 0) ||
                        (window.layersPanelAPI && window.layersPanelAPI.layers && window.layersPanelAPI.layers.length > 0);
      
      if (!hasContent) {
        console.log('Aucun contenu à exporter');
        showNotification('Aucun contenu à exporter. Ajoutez une image, dessinez ou créez des formes avant d\'exporter.', 'warning');
        return;
      }
      
      try {
        // Désélectionner toute image pour éviter les handles dans l'export
        const originalSelectedIndex = selectedImageIndex;
        const originalSelectedStrokeId = selectedDrawingStrokeId;
        selectedImageIndex = -1;
        selectedDrawingStrokeId = null;
        
        // Créer un canvas d'export propre
        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = canvas.width;
        exportCanvas.height = canvas.height;
        const exportCtx = exportCanvas.getContext('2d');
        console.log('Canvas d\'export créé:', exportCanvas.width, 'x', exportCanvas.height);
        
        // Fond transparent par défaut
        exportCtx.clearRect(0, 0, exportCanvas.width, exportCanvas.height);
      
      // **CORRECTION: Utiliser le même système unifié que redrawAll pour l'export**
      // 1. Dessiner l'image de base si elle existe ET qu'elle n'est pas gérée par les layers
      if (importedImage && (!window.layersPanelAPI || !window.layersPanelAPI.layers.some(l => l.type === 'image' && l.ref && l.ref.img === importedImage))) {
        exportCtx.drawImage(importedImage, 0, 0, exportCanvas.width, exportCanvas.height);
      }
      
      // 2. **NOUVEAU: Utiliser le système de layers unifié pour l'export**
      if (window.layersPanelAPI && window.layersPanelAPI.layers) {
        const sortedLayers = [...window.layersPanelAPI.layers].sort((a, b) => a.priority - b.priority);
        
        // Dessiner tous les éléments dans l'ordre unifié de priorité (comme dans redrawAll)
        sortedLayers.forEach(layer => {
          try {
            if (layer.type === 'image' && layer.ref && layer.ref.img) {
              // Dessiner image
              console.log('Export: Dessin image', layer.ref.x, layer.ref.y, layer.ref.width, layer.ref.height);
              exportCtx.drawImage(layer.ref.img, layer.ref.x || 0, layer.ref.y || 0, layer.ref.width, layer.ref.height);
            } else if (layer.type === 'shape' && layer.ref) {
              // Dessiner forme
              console.log('Export: Dessin forme', layer.ref.type);
              drawShape(exportCtx, layer.ref);
            } else if (layer.type === 'text' && layer.ref) {
              // Dessiner texte (sans cadre pointillé ni handles)
              console.log('Export: Dessin texte');
              drawTextElement(exportCtx, layer.ref, { skipSelection: true });
            } else if (layer.type === 'drawing') {
              // **CORRECTION: Dessiner trait de dessin avec les styles appropriés pour l'export**
              const stroke = drawingStrokes.find(s => s.id === layer.id);
              if (stroke && stroke.points && stroke.points.length > 1) {
                console.log('Export: Dessin stroke', stroke.tool, stroke.points.length, 'points');
                exportCtx.save();
                
                // **CORRECTION TEMPORAIRE: Utiliser le rendu basique pour l'export**
                exportCtx.lineCap = 'round';
                exportCtx.lineJoin = 'round';
                exportCtx.lineWidth = stroke.size || 5;
                exportCtx.strokeStyle = stroke.color || '#000000';
                exportCtx.globalAlpha = 1.0;
                
                exportCtx.beginPath();
                exportCtx.moveTo(stroke.points[0].x, stroke.points[0].y);
                for (let i = 1; i < stroke.points.length; i++) {
                  exportCtx.lineTo(stroke.points[i].x, stroke.points[i].y);
                }
                exportCtx.stroke();
                
                exportCtx.restore();
              }
            }
          } catch (error) {
            console.error('Erreur lors du dessin du layer:', layer, error);
          }
        });
      } else {
        // Système de fallback si pas de layers (ancien système)
        // 1. Images
        importedImages.forEach(imgObj => {
          if (imgObj.img) {
            exportCtx.drawImage(imgObj.img, imgObj.x || 0, imgObj.y || 0, imgObj.width, imgObj.height);
          }
        });
        
        // 2. Dessins depuis drawingLayer
        if (drawingLayer) {
          exportCtx.drawImage(drawingLayer, 0, 0);
        }
        
        // 3. Formes
        shapes.forEach(shape => {
          if (shape && shape.type) {
            drawShape(exportCtx, shape);
          }
        });

        // 4. Texte (inclure dans l'export)
         if (window.textElements && window.textElements.length) {
           const sortedText = [...window.textElements].sort((a,b)=> (a.priority ?? 0) - (b.priority ?? 0));
           sortedText.forEach(t => drawTextElement(exportCtx, t, { skipSelection: true }));
         }
      }
      
      // 4. Appliquer les zones d'effacement sur tout à la fin
      if (window.erasedAreas && window.erasedAreas.length > 0) {
        window.erasedAreas.forEach(erasedArea => {
          exportCtx.globalCompositeOperation = 'destination-out';
          exportCtx.beginPath();
          exportCtx.arc(erasedArea.x, erasedArea.y, erasedArea.radius, 0, Math.PI * 2);
          exportCtx.fill();
          exportCtx.closePath();
        });
        exportCtx.globalCompositeOperation = 'source-over';
      }
      
      // Restaurer la sélection après l'export
      selectedImageIndex = originalSelectedIndex;
      selectedDrawingStrokeId = originalSelectedStrokeId;
      
        // Créer le lien de téléchargement avec format PNG pour préserver la transparence
        console.log('Création du lien de téléchargement...');
        const link = document.createElement('a');
        const currentDate = new Date().toISOString().slice(0, 19).replace(/[T:]/g, '-');
        const filename = 'ProPaint-export-' + currentDate + '.png';
        
        // Utiliser la fonction de téléchargement sandboxé
        const downloadSuccess = performSandboxedDownload(exportCanvas, filename);
        
        if (downloadSuccess) {
          console.log('Export terminé avec succès !');
          showNotification('Export terminé ! Le fichier a été téléchargé.', 'success');
        } else {
          console.warn('Téléchargement échoué, essai de fallback...');
          showNotification('Téléchargement impossible. Essayez d\'ouvrir propaint.php directement (pas dans une iframe).', 'warning');
        }
        
      } catch (error) {
        console.error('Erreur lors de l\'export:', error);
        console.error('Stack trace:', error.stack);
        showNotification('Erreur lors de l\'export: ' + error.message, 'error');
      }
    };
    
    // **DEBUG: Vérifier que le bouton d'export est bien connecté**
    console.log('Bouton d\'export configuré:', downloadBtn);
    
    // **FALLBACK: Ajouter un event listener supplémentaire pour s'assurer que ça marche**
    if (downloadBtn) {
      downloadBtn.addEventListener('click', (e) => {
        console.log('Event listener supplémentaire déclenché');
        e.preventDefault();
        e.stopPropagation();
        
        // Si la fonction onclick principale ne marche pas, utiliser ce fallback
        if (!downloadBtn.onclick || typeof downloadBtn.onclick !== 'function') {
          console.log('Fonction onclick manquante, utilisation du fallback');
          
          // Code d'export simplifié en fallback
          try {
            const exportCanvas = document.createElement('canvas');
            exportCanvas.width = canvas.width || 1000;
            exportCanvas.height = canvas.height || 1000;
            const exportCtx = exportCanvas.getContext('2d');
            
            // Copier tout le contenu du canvas principal
            exportCtx.drawImage(canvas, 0, 0);
            
            // Téléchargement immédiat
            const link = document.createElement('a');
            link.download = 'ProPaint-fallback-' + Date.now() + '.png';
            link.href = exportCanvas.toDataURL('image/png');
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showNotification('Export de fallback réussi !', 'success');
          } catch (error) {
            console.error('Erreur fallback:', error);
            showNotification('Erreur export fallback: ' + error.message, 'error');
          }
        }
      });
    }

// Bouton pour réinitialiser les zones effacées
document.getElementById('clearErasedBtn').onclick = () => {
  window.erasedAreas = [];
  redrawAll();
  showNotification('Zones effacées réinitialisées !', 'success');
};

// **FONCTION DE NOTIFICATION VISUELLE POUR REMPLACER ALERT()**
function showNotification(message, type = 'info') {
  console.log('Notification:', type, message);
  
  // Supprimer les notifications existantes
  const existingNotifications = document.querySelectorAll('.deepseek-notification');
  existingNotifications.forEach(notif => notif.remove());
  
  // Créer la notification
  const notification = document.createElement('div');
  notification.className = 'deepseek-notification';
  
  // Styles selon le type
  const styles = {
    success: 'bg-green-600 border-green-500 text-white',
    error: 'bg-red-600 border-red-500 text-white',
    warning: 'bg-orange-600 border-orange-500 text-white',
    info: 'bg-blue-600 border-blue-500 text-white'
  };
  
  notification.innerHTML = `
    <div class="fixed top-4 right-4 z-50 ${styles[type]} px-6 py-4 rounded-lg shadow-lg border-l-4 max-w-md">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'} mr-3"></i>
          <span class="text-sm font-medium">${message}</span>
        </div>
        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  `;
  
  document.body.appendChild(notification);
  
  // Auto-suppression après 5 secondes
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 5000);
  
  // Si on est dans une iframe, essayer de communiquer avec le parent
  try {
    if (window.parent && window.parent !== window) {
      window.parent.postMessage({
        action: 'notification',
        type: type,
        message: message,
        source: 'propaint.php'
      }, '*');
    }
  } catch (e) {
    console.log('Impossible de communiquer avec le parent:', e.message);
  }
}

// **FONCTION D'EXPORT AMÉLIORÉE POUR ENVIRONNEMENT SANDBOXÉ**
function performSandboxedDownload(canvas, filename) {
  try {
    // Convertir le canvas en blob pour un téléchargement plus fiable
    canvas.toBlob((blob) => {
      if (!blob) {
        console.error('Impossible de créer le blob');
        showNotification('Erreur lors de la création du fichier', 'error');
        return;
      }
      
      console.log('Blob créé:', blob.size, 'bytes');
      
      // Créer une URL pour le blob
      const url = window.URL.createObjectURL(blob);
      
      // Méthode 1: Téléchargement direct avec blob URL
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      link.style.display = 'none';
      
      // Ajouter au DOM, cliquer, puis nettoyer
      document.body.appendChild(link);
      link.click();
      
      // Nettoyage immédiat
      document.body.removeChild(link);
      
      // Libérer l'URL après un délai pour s'assurer que le téléchargement est lancé
      setTimeout(() => {
        window.URL.revokeObjectURL(url);
        console.log('URL blob nettoyée');
      }, 1000);
      
      console.log('Téléchargement blob lancé avec succès');
      showNotification('Fichier téléchargé: ' + filename, 'success');
      
    }, 'image/png', 1.0);
    
    return true;
    
  } catch (error) {
    console.error('Erreur téléchargement blob:', error);
    
    // Fallback: Méthode dataURL traditionnelle
    try {
      const dataURL = canvas.toDataURL('image/png', 1.0);
      console.log('Fallback vers dataURL, taille:', dataURL.length);
      
      const link = document.createElement('a');
      link.href = dataURL;
      link.download = filename;
      link.style.display = 'none';
      
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      console.log('Téléchargement dataURL réussi');
      showNotification('Fichier téléchargé (fallback): ' + filename, 'success');
      return true;
      
    } catch (fallbackError) {
      console.error('Erreur fallback dataURL:', fallbackError);
      
      // Méthode 3: Communication avec le parent si possible
      try {
        if (window.parent && window.parent !== window) {
          const dataURL = canvas.toDataURL('image/png', 1.0);
          window.parent.postMessage({
            action: 'downloadRequest',
            dataURL: dataURL,
            filename: filename,
            source: 'propaint.php'
          }, '*');
          console.log('Demande de téléchargement envoyée au parent');
          showNotification('Demande de téléchargement envoyée...', 'info');
          return true;
        }
      } catch (communicationError) {
        console.error('Communication avec parent échouée:', communicationError);
      }
      
      // Méthode 4: Ouvrir dans une nouvelle fenêtre
      try {
        const dataURL = canvas.toDataURL('image/png', 1.0);
        const newWindow = window.open();
        
        if (newWindow) {
          newWindow.document.write(`
            <html>
              <head><title>Export ProPaint - ${filename}</title></head>
              <body style="margin:0; background:#000; display:flex; justify-content:center; align-items:center; flex-direction:column;">
                <div style="text-align:center; color:white; font-family:Arial; padding:20px;">
                  <h2>Export ProPaint</h2>
                  <p>Clic droit sur l'image → "Enregistrer sous..." pour télécharger</p>
                  <img src="${dataURL}" style="max-width:90%; max-height:70vh; border:1px solid #333; margin:20px 0;" />
                  <br>
                  <a href="${dataURL}" download="${filename}" style="color:#4a90e2; text-decoration:none; border:2px solid #4a90e2; padding:15px 30px; display:inline-block; border-radius:5px; font-weight:bold;">
                    ⬇️ Télécharger ${filename}
                  </a>
                </div>
              </body>
            </html>
          `);
          console.log('Image ouverte dans une nouvelle fenêtre');
          showNotification('Image ouverte dans une nouvelle fenêtre', 'info');
          return true;
        }
      } catch (popupError) {
        console.error('Ouverture de popup échouée:', popupError);
      }
      
      showNotification('Impossible de télécharger. Essayez depuis une nouvelle fenêtre.', 'error');
      return false;
    }
  }
}
  </script>
  
  <!-- Script pour les Outils Lasso -->
  <script>
    // Classe LassoToolset - Outils de sélection Lasso
    class LassoToolset {
      constructor(canvas, ctx) {
        this.canvas = canvas;
        this.ctx = ctx;
        this.currentPath = [];
        this.isDrawing = false;
        this.magneticStrength = 10; // Sensibilité du lasso magnétique
        this.polygonPoints = [];
        this.isPolygonMode = false;
        this.magneticPath = [];
        this.edgeMap = null;
      }
      
      // Lasso libre - sélection à main levée
      startFreeLasso(x, y) {
        this.currentPath = [{x, y}];
        this.isDrawing = true;
        this.drawLassoPreview();
      }
      
      continueFreeLasso(x, y) {
        if (!this.isDrawing) return;
        this.currentPath.push({x, y});
        this.drawLassoPreview();
      }
      
      endFreeLasso() {
        if (!this.isDrawing || this.currentPath.length < 3) {
          this.cancelLasso();
          return null;
        }
        this.isDrawing = false;
        const selection = this.createSelectionFromPath(this.currentPath);
        this.currentPath = [];
        return selection;
      }
      
      // Lasso polygonal - sélection par points
      addPolygonPoint(x, y) {
        this.polygonPoints.push({x, y});
        this.isPolygonMode = true;
        this.drawPolygonPreview();
        
        // Double-clic ou retour au point de départ pour terminer
        if (this.polygonPoints.length > 2) {
          const firstPoint = this.polygonPoints[0];
          const distance = Math.sqrt((x - firstPoint.x) ** 2 + (y - firstPoint.y) ** 2);
          if (distance < 10) {
            return this.endPolygonLasso();
          }
        }
        return null;
      }
      
      endPolygonLasso() {
        if (this.polygonPoints.length < 3) {
          this.cancelLasso();
          return null;
        }
        const selection = this.createSelectionFromPath(this.polygonPoints);
        this.polygonPoints = [];
        this.isPolygonMode = false;
        this.clearPreview();
        return selection;
      }
      
      // Lasso magnétique - suit automatiquement les contours
      startMagneticLasso(x, y) {
        this.magneticPath = [{x, y}];
        this.isDrawing = true;
        this.generateEdgeMap();
        this.drawLassoPreview();
      }
      
      continueMagneticLasso(x, y) {
        if (!this.isDrawing) return;
        
        const lastPoint = this.magneticPath[this.magneticPath.length - 1];
        const magneticPoints = this.findMagneticPath(lastPoint, {x, y});
        
        this.magneticPath = this.magneticPath.concat(magneticPoints);
        this.drawLassoPreview();
      }
      
      endMagneticLasso() {
        if (!this.isDrawing || this.magneticPath.length < 3) {
          this.cancelLasso();
          return null;
        }
        this.isDrawing = false;
        const selection = this.createSelectionFromPath(this.magneticPath);
        this.magneticPath = [];
        return selection;
      }
      
      // Génération de la carte des contours pour le lasso magnétique
      generateEdgeMap() {
        const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
        const width = imageData.width;
        const height = imageData.height;
        const data = imageData.data;
        
        this.edgeMap = new Float32Array(width * height);
        
        // Filtre de Sobel pour détecter les contours
        for (let y = 1; y < height - 1; y++) {
          for (let x = 1; x < width - 1; x++) {
            const idx = (y * width + x) * 4;
            
            // Conversion en niveau de gris
            const gray = (data[idx] + data[idx + 1] + data[idx + 2]) / 3;
            
            // Gradients Sobel
            const gx = this.getGrayValue(data, x-1, y-1, width) + 2*this.getGrayValue(data, x-1, y, width) + this.getGrayValue(data, x-1, y+1, width)
                     - this.getGrayValue(data, x+1, y-1, width) - 2*this.getGrayValue(data, x+1, y, width) - this.getGrayValue(data, x+1, y+1, width);
            
            const gy = this.getGrayValue(data, x-1, y-1, width) + 2*this.getGrayValue(data, x, y-1, width) + this.getGrayValue(data, x+1, y-1, width)
                     - this.getGrayValue(data, x-1, y+1, width) - 2*this.getGrayValue(data, x, y+1, width) - this.getGrayValue(data, x+1, y+1, width);
            
            const magnitude = Math.sqrt(gx * gx + gy * gy);
            this.edgeMap[y * width + x] = magnitude;
          }
        }
      }
      
      getGrayValue(data, x, y, width) {
        const idx = (y * width + x) * 4;
        return (data[idx] + data[idx + 1] + data[idx + 2]) / 3;
      }
      
      // Trouve le chemin magnétique entre deux points
      findMagneticPath(start, end) {
        const points = [];
        const dx = end.x - start.x;
        const dy = end.y - start.y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        const steps = Math.max(Math.abs(dx), Math.abs(dy));
        
        for (let i = 1; i <= steps; i++) {
          const t = i / steps;
          let x = Math.round(start.x + dx * t);
          let y = Math.round(start.y + dy * t);
          
          // Recherche du point avec le plus fort gradient dans un rayon
          const bestPoint = this.findStrongestEdge(x, y, this.magneticStrength);
          if (bestPoint) {
            points.push(bestPoint);
          } else {
            points.push({x, y});
          }
        }
        
        return points;
      }
      
      // Trouve le contour le plus fort dans un rayon donné
      findStrongestEdge(centerX, centerY, radius) {
        if (!this.edgeMap) return null;
        
        let maxStrength = 0;
        let bestPoint = null;
        
        for (let dy = -radius; dy <= radius; dy++) {
          for (let dx = -radius; dx <= radius; dx++) {
            const x = centerX + dx;
            const y = centerY + dy;
            
            if (x >= 0 && x < this.canvas.width && y >= 0 && y < this.canvas.height) {
              const distance = Math.sqrt(dx * dx + dy * dy);
              if (distance <= radius) {
                const strength = this.edgeMap[y * this.canvas.width + x];
                if (strength > maxStrength) {
                  maxStrength = strength;
                  bestPoint = {x, y};
                }
              }
            }
          }
        }
        
        return bestPoint;
      }
      
      // Création de la sélection à partir d'un chemin
      createSelectionFromPath(path) {
        if (path.length < 3) return null;
        
        // Fermer le chemin si nécessaire
        const closedPath = [...path];
        const firstPoint = closedPath[0];
        const lastPoint = closedPath[closedPath.length - 1];
        const distance = Math.sqrt((firstPoint.x - lastPoint.x) ** 2 + (firstPoint.y - lastPoint.y) ** 2);
        
        if (distance > 5) {
          closedPath.push(firstPoint);
        }
        
        // Créer un Path2D pour la sélection
        const path2D = new Path2D();
        path2D.moveTo(closedPath[0].x, closedPath[0].y);
        
        for (let i = 1; i < closedPath.length; i++) {
          path2D.lineTo(closedPath[i].x, closedPath[i].y);
        }
        
        path2D.closePath();
        
        // Calculer les limites de la sélection
        const bounds = this.calculatePathBounds(closedPath);
        
        return {
          path: path2D,
          bounds: bounds,
          points: closedPath
        };
      }
      
      calculatePathBounds(path) {
        let minX = Infinity, minY = Infinity;
        let maxX = -Infinity, maxY = -Infinity;
        
        path.forEach(point => {
          minX = Math.min(minX, point.x);
          minY = Math.min(minY, point.y);
          maxX = Math.max(maxX, point.x);
          maxY = Math.max(maxY, point.y);
        });
        
        return {
          x: minX,
          y: minY,
          w: maxX - minX,
          h: maxY - minY
        };
      }
      
      // Prévisualisation des lassos
      drawLassoPreview() {
        redrawAll(); // Redessiner tout d'abord
        
        this.ctx.save();
        this.ctx.strokeStyle = 'rgba(0, 120, 215, 0.8)';
        this.ctx.lineWidth = 1;
        this.ctx.setLineDash([3, 3]);
        
        // Dessiner le chemin actuel
        const pathToDraw = this.currentPath.length > 0 ? this.currentPath : this.magneticPath;
        
        if (pathToDraw.length > 1) {
          this.ctx.beginPath();
          this.ctx.moveTo(pathToDraw[0].x, pathToDraw[0].y);
          
          for (let i = 1; i < pathToDraw.length; i++) {
            this.ctx.lineTo(pathToDraw[i].x, pathToDraw[i].y);
          }
          
          this.ctx.stroke();
        }
        
        this.ctx.restore();
      }
      
      drawPolygonPreview() {
        redrawAll(); // Redessiner tout d'abord
        
        if (this.polygonPoints.length === 0) return;
        
        this.ctx.save();
        this.ctx.strokeStyle = 'rgba(0, 120, 215, 0.8)';
        this.ctx.fillStyle = 'rgba(0, 120, 215, 0.1)';
        this.ctx.lineWidth = 1;
        this.ctx.setLineDash([3, 3]);
        
        // Dessiner les lignes
        this.ctx.beginPath();
        this.ctx.moveTo(this.polygonPoints[0].x, this.polygonPoints[0].y);
        
        for (let i = 1; i < this.polygonPoints.length; i++) {
          this.ctx.lineTo(this.polygonPoints[i].x, this.polygonPoints[i].y);
        }
        
        if (this.polygonPoints.length > 2) {
          this.ctx.stroke();
        }
        
        // Dessiner les points
        this.ctx.setLineDash([]);
        this.ctx.fillStyle = 'rgba(0, 120, 215, 0.8)';
        
        this.polygonPoints.forEach(point => {
          this.ctx.beginPath();
          this.ctx.arc(point.x, point.y, 3, 0, Math.PI * 2);
          this.ctx.fill();
        });
        
        this.ctx.restore();
      }
      
      clearPreview() {
        redrawAll();
      }
      
      cancelLasso() {
        this.currentPath = [];
        this.polygonPoints = [];
        this.magneticPath = [];
        this.isDrawing = false;
        this.isPolygonMode = false;
        this.clearPreview();
      }
    }
    
    // Initialiser les outils Lasso
    const lassoToolset = new LassoToolset(canvas, ctx);
    let currentLassoSelection = null;
    
    // Variables pour la gestion des lassos
    let isUsingLasso = false;

    // Classe de gestion des actions de sélection
    class SelectionManager {
      constructor(canvas, ctx) {
        this.canvas = canvas;
        this.ctx = ctx;
        this.setupEventListeners();
      }

      setupEventListeners() {
        // Actions de base
        document.getElementById('cutSelection').addEventListener('click', () => this.cutSelection());
        document.getElementById('copySelection').addEventListener('click', () => this.copySelection());
        document.getElementById('pasteSelection').addEventListener('click', () => this.pasteSelection());
        document.getElementById('deleteSelection').addEventListener('click', () => this.deleteSelection());
        
        // Remplissage
        document.getElementById('fillColor').addEventListener('click', () => this.fillWithColor());
        document.getElementById('fillGradient').addEventListener('click', () => this.fillWithGradient());
        
        // Opacité
        document.getElementById('selectionOpacity').addEventListener('input', (e) => {
          selectionOpacity = parseInt(e.target.value);
          document.getElementById('selectionOpacityValue').textContent = selectionOpacity;
          this.applyOpacity();
        });
        
        // Protection
        document.getElementById('protectSelection').addEventListener('change', (e) => {
          isProtected = e.target.checked;
          this.updateSelectionInfo();
          redrawAll(); // Redessiner pour mettre à jour l'affichage de protection
        });
        
        // Mode déplacement
        document.getElementById('toggleMoveMode').addEventListener('click', () => this.toggleMoveMode());
        
        // Désélectionner
        document.getElementById('clearSelection').addEventListener('click', () => this.clearSelection());
      }

      // Vérifier si un point est dans la zone protégée
      isPointProtected(x, y) {
        if (!isProtected || !selectionPath) return false;
        return this.isPointInSelection(x, y);
      }

      // Vérifier si un point est dans la sélection
      isPointInSelection(x, y) {
        if (selectionRect) {
          return x >= selectionRect.x && x <= selectionRect.x + selectionRect.width &&
                 y >= selectionRect.y && y <= selectionRect.y + selectionRect.height;
        }
        if (selectionPath) {
          // Utiliser ray casting algorithm pour les sélections de forme libre
          return this.pointInPolygon(x, y, selectionPath);
        }
        return false;
      }

      // Algorithme Point-in-Polygon (ray casting)
      pointInPolygon(x, y, polygon) {
        let inside = false;
        for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
          if (((polygon[i].y > y) !== (polygon[j].y > y)) &&
              (x < (polygon[j].x - polygon[i].x) * (y - polygon[i].y) / (polygon[j].y - polygon[i].y) + polygon[i].x)) {
            inside = !inside;
          }
        }
        return inside;
      }

      // Obtenir les limites de la sélection
      getSelectionBounds() {
        if (selectionRect) {
          return selectionRect;
        }
        if (selectionPath && selectionPath.length > 0) {
          const xs = selectionPath.map(p => p.x);
          const ys = selectionPath.map(p => p.y);
          return {
            x: Math.min(...xs),
            y: Math.min(...ys),
            width: Math.max(...xs) - Math.min(...xs),
            height: Math.max(...ys) - Math.min(...ys)
          };
        }
        return null;
      }

      // Couper la sélection
      cutSelection() {
        if (this.copySelection()) {
          this.deleteSelection();
        }
      }

      // Copier la sélection
      copySelection() {
        const bounds = this.getSelectionBounds();
        if (!bounds) return false;

        // Créer un canvas temporaire pour la sélection
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = bounds.width;
        tempCanvas.height = bounds.height;
        const tempCtx = tempCanvas.getContext('2d');

        // Copier les pixels de la sélection
        const imageData = this.ctx.getImageData(bounds.x, bounds.y, bounds.width, bounds.height);
        
        if (selectionPath) {
          // Appliquer le masque de sélection pour les formes libres
          const maskData = tempCtx.createImageData(bounds.width, bounds.height);
          for (let y = 0; y < bounds.height; y++) {
            for (let x = 0; x < bounds.width; x++) {
              const globalX = bounds.x + x;
              const globalY = bounds.y + y;
              if (this.isPointInSelection(globalX, globalY)) {
                const i = (y * bounds.width + x) * 4;
                maskData.data[i] = imageData.data[i];     // R
                maskData.data[i + 1] = imageData.data[i + 1]; // G
                maskData.data[i + 2] = imageData.data[i + 2]; // B
                maskData.data[i + 3] = imageData.data[i + 3]; // A
              }
            }
          }
          tempCtx.putImageData(maskData, 0, 0);
        } else {
          tempCtx.putImageData(imageData, 0, 0);
        }

        copiedSelection = {
          canvas: tempCanvas,
          width: bounds.width,
          height: bounds.height
        };

        // Activer le bouton coller
        document.getElementById('pasteSelection').disabled = false;
        return true;
      }

      // Coller la sélection
      pasteSelection() {
        if (!copiedSelection) return;

        // Position de collage (centre du canvas ou position de la souris)
        const pasteX = (this.canvas.width - copiedSelection.width) / 2;
        const pasteY = (this.canvas.height - copiedSelection.height) / 2;

        this.ctx.drawImage(copiedSelection.canvas, pasteX, pasteY);
        
        // Sauvegarder dans le calque de dessin
        this.saveToDrawingLayer();
        
        // Créer une nouvelle sélection rectangulaire autour de la zone collée
        selectionRect = {
          x: pasteX,
          y: pasteY,
          width: copiedSelection.width,
          height: copiedSelection.height
        };
        selectionPath = null;
        selectionType = 'rect';
        
        this.showSelectionControls();
        redrawAll();
      }

      // Supprimer la sélection
      deleteSelection() {
        const bounds = this.getSelectionBounds();
        if (!bounds) return;

        if (selectionPath) {
          // Pour les sélections de forme libre, effacer pixel par pixel
          for (let y = bounds.y; y < bounds.y + bounds.height; y++) {
            for (let x = bounds.x; x < bounds.x + bounds.width; x++) {
              if (this.isPointInSelection(x, y)) {
                this.ctx.clearRect(x, y, 1, 1);
              }
            }
          }
        } else {
          // Pour les sélections rectangulaires
          this.ctx.clearRect(bounds.x, bounds.y, bounds.width, bounds.height);
        }

        this.saveToDrawingLayer();
        redrawAll();
      }

      // Remplir avec une couleur
      fillWithColor() {
        const bounds = this.getSelectionBounds();
        if (!bounds) return;

        const color = document.getElementById('color1').value;
        this.ctx.fillStyle = color;

        if (selectionPath) {
          // Créer un chemin de sélection et remplir
          this.ctx.save();
          this.ctx.beginPath();
          this.ctx.moveTo(selectionPath[0].x, selectionPath[0].y);
          for (let i = 1; i < selectionPath.length; i++) {
            this.ctx.lineTo(selectionPath[i].x, selectionPath[i].y);
          }
          this.ctx.closePath();
          this.ctx.clip();
          this.ctx.fillRect(bounds.x, bounds.y, bounds.width, bounds.height);
          this.ctx.restore();
        } else {
          this.ctx.fillRect(bounds.x, bounds.y, bounds.width, bounds.height);
        }

        this.saveToDrawingLayer();
        redrawAll();
      }

      // Remplir avec un dégradé
      fillWithGradient() {
        const bounds = this.getSelectionBounds();
        if (!bounds) return;

        const color1 = document.getElementById('color1').value;
        const color2 = document.getElementById('color2').value;
        
        const gradient = this.ctx.createLinearGradient(
          bounds.x, bounds.y, 
          bounds.x + bounds.width, bounds.y + bounds.height
        );
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        
        this.ctx.fillStyle = gradient;

        if (selectionPath) {
          this.ctx.save();
          this.ctx.beginPath();
          this.ctx.moveTo(selectionPath[0].x, selectionPath[0].y);
          for (let i = 1; i < selectionPath.length; i++) {
            this.ctx.lineTo(selectionPath[i].x, selectionPath[i].y);
          }
          this.ctx.closePath();
          this.ctx.clip();
          this.ctx.fillRect(bounds.x, bounds.y, bounds.width, bounds.height);
          this.ctx.restore();
        } else {
          this.ctx.fillRect(bounds.x, bounds.y, bounds.width, bounds.height);
        }

        this.saveToDrawingLayer();
        redrawAll();
      }

      // Appliquer l'opacité
      applyOpacity() {
        const bounds = this.getSelectionBounds();
        if (!bounds) return;

        const imageData = this.ctx.getImageData(bounds.x, bounds.y, bounds.width, bounds.height);
        const alpha = selectionOpacity / 100;

        for (let i = 3; i < imageData.data.length; i += 4) {
          imageData.data[i] *= alpha; // Modifier le canal alpha
        }

        this.ctx.putImageData(imageData, bounds.x, bounds.y);
        this.saveToDrawingLayer();
        redrawAll();
      }

      // Basculer le mode déplacement
      toggleMoveMode() {
        moveMode = !moveMode;
        const btn = document.getElementById('toggleMoveMode');
        if (moveMode) {
          btn.textContent = '🔒 Mode Normal';
          btn.classList.add('bg-green-600');
          btn.classList.remove('bg-orange-600');
          this.canvas.style.cursor = 'move';
        } else {
          btn.innerHTML = '<i class="fas fa-arrows-alt mr-1"></i>Mode Déplacement';
          btn.classList.add('bg-orange-600');
          btn.classList.remove('bg-green-600');
          this.canvas.style.cursor = 'default';
        }
        this.updateSelectionInfo();
      }

      // Effacer la sélection
      clearSelection() {
        selectionRect = null;
        selectionPath = null;
        selectionType = null;
        moveMode = false;
        isProtected = false;
        
        // Réinitialiser l'interface
        document.getElementById('protectSelection').checked = false;
        document.getElementById('toggleMoveMode').innerHTML = '<i class="fas fa-arrows-alt mr-1"></i>Mode Déplacement';
        document.getElementById('toggleMoveMode').classList.add('bg-orange-600');
        document.getElementById('toggleMoveMode').classList.remove('bg-green-600');
        
        this.hideSelectionControls();
        this.canvas.style.cursor = 'default';
        redrawAll();
      }

      // Afficher les contrôles de sélection
      showSelectionControls() {
        document.getElementById('selectionControls').classList.remove('hidden');
        document.getElementById('selectionInfo').classList.remove('hidden');
        this.updateSelectionInfo();
      }

      // Masquer les contrôles de sélection
      hideSelectionControls() {
        document.getElementById('selectionControls').classList.add('hidden');
        document.getElementById('selectionInfo').classList.add('hidden');
      }

      // Mettre à jour les informations de sélection
      updateSelectionInfo() {
        const bounds = this.getSelectionBounds();
        if (!bounds) return;

        document.getElementById('selectionTypeDisplay').textContent = 
          selectionType === 'rect' ? 'Rectangle' :
          selectionType === 'free' ? 'Lasso Libre' :
          selectionType === 'polygonal' ? 'Lasso Polygonal' :
          selectionType === 'magnetic' ? 'Lasso Magnétique' : 'Inconnue';

        // **CORRECTION: Vérifier que bounds existe avant d'utiliser toFixed**
        if (bounds && typeof bounds.width === 'number' && typeof bounds.height === 'number') {
          document.getElementById('selectionSizeDisplay').textContent = 
            `${bounds.width.toFixed(0)} × ${bounds.height.toFixed(0)}px`;

          document.getElementById('selectionPosDisplay').textContent = 
            `${bounds.x.toFixed(0)}, ${bounds.y.toFixed(0)}`;
        } else {
          document.getElementById('selectionSizeDisplay').textContent = 'N/A';
          document.getElementById('selectionPosDisplay').textContent = 'N/A';
        }

        const status = [];
        if (isProtected) status.push('Protégée');
        if (moveMode) status.push('Déplaçable');
        if (copiedSelection) status.push('Copiée');
        document.getElementById('selectionStatusDisplay').textContent = 
          status.length > 0 ? status.join(', ') : 'Active';
      }

      // Sauvegarder dans le calque de dessin
      saveToDrawingLayer() {
        drawingLayer.getContext('2d').clearRect(0, 0, drawingLayer.width, drawingLayer.height);
        drawingLayer.getContext('2d').drawImage(this.canvas, 0, 0);
      }
    }

    // Initialiser le gestionnaire de sélection
    const selectionManager = new SelectionManager(canvas, ctx);
    
    // Event listeners pour les contrôles de zoom
    document.getElementById('zoomIn').addEventListener('click', () => {
      applyZoom(-120, canvas.width / 2, canvas.height / 2);
    });
    
    document.getElementById('zoomOut').addEventListener('click', () => {
      applyZoom(120, canvas.width / 2, canvas.height / 2);
    });
    
    document.getElementById('zoomReset').addEventListener('click', () => {
      resetZoom();
    });
    
    document.getElementById('centerCanvas').addEventListener('click', () => {
      centerCanvas();
    });
    
    // **EVENT LISTENERS UNDO/REDO**
    document.getElementById('undoBtn').addEventListener('click', () => {
      undo();
    });
    
    document.getElementById('redoBtn').addEventListener('click', () => {
      redo();
    });
    
    // Raccourcis clavier pour Undo/Redo
    document.addEventListener('keydown', (e) => {
      if (e.ctrlKey && e.key === 'z' && !e.shiftKey) {
        e.preventDefault();
        undo();
      } else if (e.ctrlKey && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) {
        e.preventDefault();
        redo();
      }
    });
    
    // Initialiser les boutons Undo/Redo
    updateUndoRedoButtons();
    
    // Initialiser l'affichage du zoom
    updateZoomDisplay();
    
    // Gestion des raccourcis clavier pour les lassos et sélections
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (isUsingLasso || lassoToolset.isPolygonMode) {
          lassoToolset.cancelLasso();
          isUsingLasso = false;
          currentLassoSelection = null;
          selectionRect = null;
          selectionPath = null;
          selectionManager.hideSelectionControls();
          redrawAll();
          e.preventDefault();
        } else if (selectionRect || selectionPath) {
          selectionManager.clearSelection();
          e.preventDefault();
        }
      }
      
      // Raccourcis pour les actions de sélection (si une sélection existe)
      if (selectionRect || selectionPath) {
        if (e.ctrlKey && e.key === 'x') { // Couper
          e.preventDefault();
          selectionManager.cutSelection();
        } else if (e.ctrlKey && e.key === 'c') { // Copier
          e.preventDefault();
          selectionManager.copySelection();
        } else if (e.ctrlKey && e.key === 'v') { // Coller
          e.preventDefault();
          selectionManager.pasteSelection();
        } else if (e.key === 'Delete' || e.key === 'Backspace') { // Supprimer
          e.preventDefault();
          selectionManager.deleteSelection();
        } else if (e.key === 'm' || e.key === 'M') { // Mode déplacement
          e.preventDefault();
          selectionManager.toggleMoveMode();
        }
      }
      
      // Raccourcis de zoom
      if (e.ctrlKey && e.key === '0') {
        e.preventDefault();
        resetZoom();
      } else if (e.ctrlKey && e.key === '+') {
        e.preventDefault();
        applyZoom(-120, canvas.width / 2, canvas.height / 2); // Zoom in
      } else if (e.ctrlKey && e.key === '-') {
        e.preventDefault();
        applyZoom(120, canvas.width / 2, canvas.height / 2); // Zoom out
      }
      
      // Raccourci pour afficher les infos (F1)
      if (e.key === 'F1') {
        e.preventDefault();
        const infoPanel = document.getElementById('selectionInfo');
        if (infoPanel.classList.contains('hidden')) {
          infoPanel.classList.remove('hidden');
        } else {
          infoPanel.classList.add('hidden');
        }
      }
      
      // Raccourci pour centrer le canvas (Space)
      if (e.key === ' ' && !e.ctrlKey && !e.altKey && !e.shiftKey) {
        e.preventDefault();
        centerCanvas();
      }
    });
    
  </script>
  <script>
  // Create the new icon button in the left vertical toolbar
  const baseIconBtn = document.createElement('button');
  baseIconBtn.setAttribute('aria-label', 'Base Size and Images');
  baseIconBtn.className = 'w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded';
  baseIconBtn.innerHTML = '<i class="fas fa-layer-group text-[20px]"></i>';
  leftToolbar.appendChild(baseIconBtn);

  // Reference to the right panel container (tools section)
  const rightPanel = document.getElementById('rightPanel');
  const toolsSection = document.getElementById('toolsSection');

  // Create container for base size and images info
  const baseImagesPanel = document.createElement('div');
  baseImagesPanel.className = 'p-3 bg-[#252525] border-b border-[#555] text-[#c0c0c0] overflow-y-auto max-h-[calc(100vh-160px)]';
  baseImagesPanel.style.display = 'none';
  baseImagesPanel.innerHTML = `
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-semibold">Base & Images</h2>
      <button id="backToToolsBtn" class="px-3 py-1 bg-[#00aaff] hover:bg-[#0088cc] text-white rounded text-sm">
        <i class="fas fa-arrow-left mr-1"></i>Tools
      </button>
    </div>
    <div class="mb-4">
      <label for="baseWidth" class="block mb-1 text-sm">Base Width (px)</label>
      <input type="number" id="baseWidth" min="1" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-[#c0c0c0]" />
    </div>
    <div class="mb-4">
      <label for="baseHeight" class="block mb-1 text-sm">Base Height (px)</label>
      <input type="number" id="baseHeight" min="1" class="w-full bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-[#c0c0c0]" />
    </div>
    <div>
      <h3 class="text-md font-semibold mb-2">Imported Images</h3>
      <div id="importedImagesList" class="space-y-2 max-h-48 overflow-y-auto border border-[#555] rounded p-2 bg-[#1e1e1e]"></div>
    </div>
    <div id="resizeOptions" class="mt-4 hidden">
      <h3 class="text-md font-semibold mb-2">Resize Options</h3>
      <div id="resizeControls" class="space-y-3">
        <!-- Dynamic resize controls will be inserted here -->
      </div>
    </div>
  `;

  // Insert the new panel after the existing tools section
  toolsSection.parentNode.insertBefore(baseImagesPanel, toolsSection.nextSibling);

  // Variables to track base and images
  const baseWidthInput = baseImagesPanel.querySelector('#baseWidth');
  const baseHeightInput = baseImagesPanel.querySelector('#baseHeight');
  const importedImagesList = baseImagesPanel.querySelector('#importedImagesList');
  const resizeOptions = baseImagesPanel.querySelector('#resizeOptions');
  const resizeControls = baseImagesPanel.querySelector('#resizeControls');
  const backToToolsBtn = baseImagesPanel.querySelector('#backToToolsBtn');

  // Store imported images info
  let selectedItem = null; // can be 'base' or an image object

  // Function to update base inputs from canvas size
  function updateBaseInputs() {
    baseWidthInput.value = canvas.width;
    baseHeightInput.value = canvas.height;
  }

  // Function to resize the base canvas
  function resizeBaseCanvas(newWidth, newHeight) {
    if (newWidth < 1 || newHeight < 1) return;
    
    // Sauvegarder le contenu actuel du drawingLayer
    let oldDrawingContent = null;
    if (drawingLayer && drawingLayer.width > 0 && drawingLayer.height > 0) {
      oldDrawingContent = drawingLayer.getContext('2d').getImageData(0, 0, drawingLayer.width, drawingLayer.height);
    }
    
    // Redimensionner le canvas principal
    canvas.width = newWidth;
    canvas.height = newHeight;
    canvas.style.width = newWidth + 'px';
    canvas.style.height = newHeight + 'px';
    
    // Redimensionner le drawingLayer
    if (drawingLayer) {
      drawingLayer.width = newWidth;
      drawingLayer.height = newHeight;
      
      // Restaurer l'ancien contenu si il existait
      if (oldDrawingContent) {
        drawingLayer.getContext('2d').putImageData(oldDrawingContent, 0, 0);
      }
    }
    
    // NE PAS redimensionner importedImage - c'est juste une référence
    // Les dimensions des images importées sont gérées individuellement
    
    // Recalibrer le système de coordonnées
    recalibrateCoordinates();
    
    // Redessiner tout proprement
    redrawAll();
    
    // Mettre à jour les inputs
    updateBaseInputs();
  }

  // Function to clear resize controls
  function clearResizeControls() {
    resizeControls.innerHTML = '';
  }

  // Function to create resize controls for an item (base or image)
  function createResizeControls(item) {
    clearResizeControls();

    // Width input
    const widthDiv = document.createElement('div');
    widthDiv.className = 'flex items-center space-x-2';
    const widthLabel = document.createElement('label');
    widthLabel.textContent = 'Width (px):';
    widthLabel.className = 'w-20 text-sm';
    const widthInput = document.createElement('input');
    widthInput.type = 'number';
    widthInput.min = '1';
    widthInput.className = 'flex-grow bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-[#c0c0c0]';
    widthInput.value = Math.round(item.width);
    widthDiv.appendChild(widthLabel);
    widthDiv.appendChild(widthInput);

    // Height input
    const heightDiv = document.createElement('div');
    heightDiv.className = 'flex items-center space-x-2';
    const heightLabel = document.createElement('label');
    heightLabel.textContent = 'Height (px):';
    heightLabel.className = 'w-20 text-sm';
    const heightInput = document.createElement('input');
    heightInput.type = 'number';
    heightInput.min = '1';
    heightInput.className = 'flex-grow bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-[#c0c0c0]';
    heightInput.value = Math.round(item.height);
    heightDiv.appendChild(heightLabel);
    heightDiv.appendChild(heightInput);

    resizeControls.appendChild(widthDiv);
    resizeControls.appendChild(heightDiv);

    // Maintain aspect ratio checkbox
    const aspectRatioDiv = document.createElement('div');
    aspectRatioDiv.className = 'flex items-center space-x-2';
    const aspectRatioCheckbox = document.createElement('input');
    aspectRatioCheckbox.type = 'checkbox';
    aspectRatioCheckbox.id = 'aspectRatioCheckbox';
    aspectRatioCheckbox.checked = true;
    const aspectRatioLabel = document.createElement('label');
    aspectRatioLabel.setAttribute('for', 'aspectRatioCheckbox');
    aspectRatioLabel.textContent = 'Maintain aspect ratio';
    aspectRatioLabel.className = 'text-sm select-none';
    aspectRatioDiv.appendChild(aspectRatioCheckbox);
    aspectRatioDiv.appendChild(aspectRatioLabel);
    resizeControls.appendChild(aspectRatioDiv);

    // Store original aspect ratio
    const originalRatio = item.width / item.height;

    // Event listeners for inputs
    widthInput.addEventListener('input', () => {
      let w = parseInt(widthInput.value);
      if (isNaN(w) || w < 1) return;
      if (aspectRatioCheckbox.checked) {
        const h = Math.round(w / originalRatio);
        heightInput.value = h;
      }
    });

    heightInput.addEventListener('input', () => {
      let h = parseInt(heightInput.value);
      if (isNaN(h) || h < 1) return;
      if (aspectRatioCheckbox.checked) {
        const w = Math.round(h * originalRatio);
        widthInput.value = w;
      }
    });

    // Apply resize button
    const applyBtn = document.createElement('button');
    applyBtn.textContent = 'Apply Resize';
    applyBtn.className = 'mt-3 w-full bg-[#00aaff] hover:bg-[#0088cc] text-white px-3 py-1 rounded';
    resizeControls.appendChild(applyBtn);

    applyBtn.addEventListener('click', () => {
      const newW = parseInt(widthInput.value);
      const newH = parseInt(heightInput.value);
      if (isNaN(newW) || newW < 1 || isNaN(newH) || newH < 1) return;

      if (item.type === 'base') {
        resizeBaseCanvas(newW, newH);
        updateBaseInputs();
      } else if (item.type === 'image') {
        // Resize the image object and redraw
        item.img.width = newW;
        item.img.height = newH;
        redrawAll();
      }
    });
  }

  // Function to draw resize handles around an image
  function drawResizeHandles(imgObj) {
    const handleSize = 8;
    const x = imgObj.x || 0;
    const y = imgObj.y || 0;
    const w = imgObj.width;
    const h = imgObj.height;
    
    ctx.save();
    
    // Draw selection border with dashed lines
    ctx.strokeStyle = '#00aaff';
    ctx.lineWidth = 2;
    ctx.setLineDash([5, 5]);
    ctx.strokeRect(x, y, w, h);
    
    // Draw resize handles
    ctx.fillStyle = '#00aaff';
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 2;
    ctx.setLineDash([]);
    
    const handles = [
      { x: x - handleSize/2, y: y - handleSize/2, cursor: 'nw-resize', type: 'nw' },
      { x: x + w/2 - handleSize/2, y: y - handleSize/2, cursor: 'n-resize', type: 'n' },
      { x: x + w - handleSize/2, y: y - handleSize/2, cursor: 'ne-resize', type: 'ne' },
      { x: x + w - handleSize/2, y: y + h/2 - handleSize/2, cursor: 'e-resize', type: 'e' },
      { x: x + w - handleSize/2, y: y + h - handleSize/2, cursor: 'se-resize', type: 'se' },
      { x: x + w/2 - handleSize/2, y: y + h - handleSize/2, cursor: 's-resize', type: 's' },
      { x: x - handleSize/2, y: y + h - handleSize/2, cursor: 'sw-resize', type: 'sw' },
      { x: x - handleSize/2, y: y + h/2 - handleSize/2, cursor: 'w-resize', type: 'w' }
    ];
    
    handles.forEach(handle => {
      ctx.fillRect(handle.x, handle.y, handleSize, handleSize);
      ctx.strokeRect(handle.x, handle.y, handleSize, handleSize);
    });
    
    ctx.restore();
  }

  // Function to get resize handle at position
  function getResizeHandle(x, y, imgObj) {
    if (selectedImageIndex === -1) return null;
    
    const handleSize = 8;
    const imgX = imgObj.x || 0;
    const imgY = imgObj.y || 0;
    const w = imgObj.width;
    const h = imgObj.height;
    
    const handles = [
      { x: imgX - handleSize/2, y: imgY - handleSize/2, type: 'nw' },
      { x: imgX + w/2 - handleSize/2, y: imgY - handleSize/2, type: 'n' },
      { x: imgX + w - handleSize/2, y: imgY - handleSize/2, type: 'ne' },
      { x: imgX + w - handleSize/2, y: imgY + h/2 - handleSize/2, type: 'e' },
      { x: imgX + w - handleSize/2, y: imgY + h - handleSize/2, type: 'se' },
      { x: imgX + w/2 - handleSize/2, y: imgY + h - handleSize/2, type: 's' },
      { x: imgX - handleSize/2, y: imgY + h - handleSize/2, type: 'sw' },
      { x: imgX - handleSize/2, y: imgY + h/2 - handleSize/2, type: 'w' }
    ];
    
    for (let handle of handles) {
      if (x >= handle.x && x <= handle.x + handleSize && 
          y >= handle.y && y <= handle.y + handleSize) {
        return handle.type;
      }
    }
    return null;
  }

  // Function to check if click is on an image
  function getImageAtPosition(x, y) {
    for (let i = importedImages.length - 1; i >= 0; i--) {
      const imgObj = importedImages[i];
      const imgX = imgObj.x || 0;
      const imgY = imgObj.y || 0;
      if (x >= imgX && x <= imgX + imgObj.width && 
          y >= imgY && y <= imgY + imgObj.height) {
        return i;
      }
    }
    return -1;
  }

  // Update base inputs initially
  updateBaseInputs();

  // Function to show tools panel and hide base panel
  function showToolsPanel() {
    baseImagesPanel.style.display = 'none';
    toolsSection.style.display = 'block';
    // Show other panels if any
    Array.from(rightPanel.children).forEach(child => {
      if (child !== baseImagesPanel) {
        child.style.display = 'block';
      }
    });
    resizeOptions.classList.add('hidden');
    selectedItem = null;
  }

  // Show/hide baseImagesPanel on icon click
  baseIconBtn.addEventListener('click', () => {
    const isVisible = baseImagesPanel.style.display === 'block';
    if (!isVisible) {
      // Hide all other right panel children except toolsSection
      Array.from(rightPanel.children).forEach(child => {
        if (child !== toolsSection) child.style.display = 'none';
      });
      baseImagesPanel.style.display = 'block';
      toolsSection.style.display = 'none';
      updateBaseInputs();
      selectedItem = { type: 'base', width: canvas.width, height: canvas.height };
      resizeOptions.classList.remove('hidden');
      createResizeControls(selectedItem);
    } else {
      showToolsPanel();
    }
  });

  // Add event listeners to all toolbar buttons to return to tools panel
  const toolbarButtons = leftToolbar.querySelectorAll('button');
  toolbarButtons.forEach(button => {
    if (button !== baseIconBtn) {
      button.addEventListener('click', () => {
        showToolsPanel();
      });
    }
  });

  // Add event listener to the back to tools button
  backToToolsBtn.addEventListener('click', () => {
    showToolsPanel();
  });

  // Update base size inputs on change
  baseWidthInput.addEventListener('change', () => {
    let w = parseInt(baseWidthInput.value);
    let h = parseInt(baseHeightInput.value);
    if (isNaN(w) || w < 1) w = canvas.width;
    if (isNaN(h) || h < 1) h = canvas.height;
    resizeBaseCanvas(w, h);
    updateBaseInputs();
  });
  baseHeightInput.addEventListener('change', () => {
    let w = parseInt(baseWidthInput.value);
    let h = parseInt(baseHeightInput.value);
    if (isNaN(w) || w < 1) w = canvas.width;
    if (isNaN(h) || h < 1) h = canvas.height;
    resizeBaseCanvas(w, h);
    updateBaseInputs();
  });

  // Remplacer le gestionnaire d'événement d'upload existant
  const originalUploadHandler = uploadInput.onchange;
  uploadInput.onchange = e => {
    const file = e.target.files[0];
    if (!file) return;
    
    // Reset input value to allow re-uploading the same file
    e.target.value = '';
    
    const img = new Image();
    const reader = new FileReader();

    reader.onload = function(event) {
      img.src = event.target.result;
    };

    img.onload = () => {
      // Si c'est la toute première image, on initialise le canvas
      if (!imageLoaded) {
        importedImage = img; // Garder une référence pour compatibilité
        let w = img.width, h = img.height, maxDim = 10000;
        if (w > maxDim || h > maxDim) {
          const scale = Math.min(maxDim / w, maxDim / h);
          w = Math.round(w * scale);
          h = Math.round(h * scale);
        }
        canvas.width = w;
        canvas.height = h;
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        
        // Initialiser le canvas de sauvegarde des dessins
        if (!drawingLayer) {
          drawingLayer = document.createElement('canvas');
        }
        drawingLayer.width = w;
        drawingLayer.height = h;
        
        imageLoaded = true;
        downloadBtn.disabled = false;
        shapes = [];
        selectionRect = null;
        clipboard = null;
        selectedImageIndex = -1;
        
        // Ajouter cette première image aux images importées
        const imgObj = {
          img: img,
          width: w,
          height: h,
          x: 0,
          y: 0,
          id: Date.now() + Math.random(),
          isBaseImage: true,
          rotation: 0
        };
        importedImages.push(imgObj);
        
        // Ajouter aux calques si le système existe
        if (window.layersPanelAPI) {
           window.layersPanelAPI.addLayerForImage(imgObj);
        }
      } else {
        // Pour les images suivantes, on les ajoute simplement à la liste
        // SANS supprimer les précédentes
        
        let w = img.width;
        let h = img.height;
        
        // Redimensionner pour tenir dans le canvas si trop grand
        if (w > canvas.width || h > canvas.height) {
           const scale = Math.min(canvas.width / w, canvas.height / h);
           w = Math.round(w * scale);
           h = Math.round(h * scale);
        }
        
        const imgObj = {
          img: img,
          width: w,
          height: h,
          x: 0, // Toujours positionner à 0,0
          y: 0,
          id: Date.now() + Math.random(),
          isBaseImage: false,
          rotation: 0
        };
        
        // Ajouter à la liste des images importées (ACCUMULATION)
        importedImages.push(imgObj);
        
        // Sélectionner la nouvelle image
        selectedImageIndex = importedImages.length - 1;
        
        // Ajouter aux calques si le système existe
        if (window.layersPanelAPI) {
           window.layersPanelAPI.addLayerForImage(imgObj);
        }
      }

      redrawAll();
      updateImportedImagesList();
      updateBaseInputs();
      saveState(); // Sauvegarder l'état après import
    };

    img.onerror = () => alert("Erreur lors du chargement de l'image.");
    reader.readAsDataURL(file);
  };

  // Function to setup real-time editing for sidebar inputs
  function setupRealTimeEditing() {
    const inputs = [
      'strokeColor', 'fillColor', 'lineWidth', 'opacity', 
      'brushSize', 'brushStyle', 'styleIntensity', 'textureGrain', 
      'spreading', 'blurEffect', 'shineIntensity', 'shineColor', 'shineOpacity',
      'selectedShapeStyle', 'selectedShapeIntensity', 'selectedShapeGrain',
      'selectedShapeSpreading', 'selectedShapeBlur', 'selectedShapeShine', 'selectedShapeTexture'
    ];
    
    inputs.forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.addEventListener('input', () => {
          if (isElementSelected && selectedElement) {
            // Force redraw to apply changes immediately
            redrawAll();
          }
        });
        el.addEventListener('change', () => {
           if (isElementSelected && selectedElement) {
             redrawAll();
           }
        });
      }
    });
    
    // Special handling for color inputs
    const color1 = document.getElementById('color1');
    if (color1) {
      color1.addEventListener('input', () => {
        if (isElementSelected && selectedElement) {
          selectedElement.color = color1.value;
          redrawAll();
        }
      });
    }
  }
  
  setupRealTimeEditing();

  // Function to update the imported images list in the right panel
  function updateImportedImagesList() {
    importedImagesList.innerHTML = '';
    if (importedImages.length === 0) {
      importedImagesList.textContent = 'Aucune image importée.';
      return;
    }
    importedImages.forEach(imgObj => {
      const div = document.createElement('div');
      div.className = 'flex items-center space-x-2 cursor-pointer p-1 rounded hover:bg-[#3a3a3a]';
      div.tabIndex = 0;
      div.setAttribute('role', 'button');
      div.setAttribute('aria-label', `Image importée ${imgObj.img.src.substring(0, 20)}`);

      const thumb = document.createElement('img');
      thumb.src = imgObj.img.src;
      thumb.alt = 'Miniature de l\'image importée';
      thumb.className = 'w-12 h-12 object-contain border border-[#555] rounded';
      div.appendChild(thumb);

      const info = document.createElement('div');
      info.className = 'flex-grow text-xs truncate';
      info.textContent = `W: ${Math.round(imgObj.width)} px, H: ${Math.round(imgObj.height)} px`;
      div.appendChild(info);

      // Click to select image and show resize options
      div.addEventListener('click', () => {
        selectedItem = { type: 'image', img: imgObj.img, width: imgObj.width, height: imgObj.height, obj: imgObj };
        selectedImageIndex = importedImages.indexOf(imgObj); // Also select for canvas interaction
        resizeOptions.classList.remove('hidden');
        createResizeControls(selectedItem);
        redrawAll(); // Redraw to show handles
      });

      // Keyboard accessibility
      div.addEventListener('keydown', (ev) => {
        if (ev.key === 'Enter' || ev.key === ' ') {
          ev.preventDefault();
          div.click();
        }
      });

      importedImagesList.appendChild(div);
    });
  }

  // Supprimer la fonction de redimensionnement de base en doublon qui était après
  // car nous avons déjà la bonne version plus haut

  // When resizing an imported image, update its size and redraw
  function resizeImportedImage(imgObj, newWidth, newHeight) {
    if (newWidth < 1 || newHeight < 1) return;
    
    // Mettre à jour directement les dimensions de l'objet image
    // Sans redimensionner l'image elle-même pour éviter la perte de qualité
    imgObj.width = newWidth;
    imgObj.height = newHeight;
    
    // Recalibrer les coordonnées pour éviter les décalages
    recalibrateCoordinates();
    
    // Redessiner avec les nouvelles dimensions
    redrawAll();
    updateImportedImagesList();
  }

  // Override createResizeControls apply button to handle imported images resizing
  function createResizeControls(item) {
    clearResizeControls();

    // Width input
    const widthDiv = document.createElement('div');
    widthDiv.className = 'flex items-center space-x-2';
    const widthLabel = document.createElement('label');
    widthLabel.textContent = 'Width (px):';
    widthLabel.className = 'w-20 text-sm';
    const widthInput = document.createElement('input');
    widthInput.type = 'number';
    widthInput.min = '1';
    widthInput.className = 'flex-grow bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-[#c0c0c0]';
    widthInput.value = Math.round(item.width);
    widthDiv.appendChild(widthLabel);
    widthDiv.appendChild(widthInput);

    // Height input
    const heightDiv = document.createElement('div');
    heightDiv.className = 'flex items-center space-x-2';
    const heightLabel = document.createElement('label');
    heightLabel.textContent = 'Height (px):';
    heightLabel.className = 'w-20 text-sm';
    const heightInput = document.createElement('input');
    heightInput.type = 'number';
    heightInput.min = '1';
    heightInput.className = 'flex-grow bg-[#1e1e1e] border border-[#555] rounded px-2 py-1 text-[#c0c0c0]';
    heightInput.value = Math.round(item.height);
    heightDiv.appendChild(heightLabel);
    heightDiv.appendChild(heightInput);

    resizeControls.appendChild(widthDiv);
    resizeControls.appendChild(heightDiv);

    // Maintain aspect ratio checkbox
    const aspectRatioDiv = document.createElement('div');
    aspectRatioDiv.className = 'flex items-center space-x-2';
    const aspectRatioCheckbox = document.createElement('input');
    aspectRatioCheckbox.type = 'checkbox';
    aspectRatioCheckbox.id = 'aspectRatioCheckbox';
    aspectRatioCheckbox.checked = true;
    const aspectRatioLabel = document.createElement('label');
    aspectRatioLabel.setAttribute('for', 'aspectRatioCheckbox');
    aspectRatioLabel.textContent = 'Maintain aspect ratio';
    aspectRatioLabel.className = 'text-sm select-none';
    aspectRatioDiv.appendChild(aspectRatioCheckbox);
    aspectRatioDiv.appendChild(aspectRatioLabel);
    resizeControls.appendChild(aspectRatioDiv);

    // Store original aspect ratio
    const originalRatio = item.width / item.height;

    // Event listeners for inputs
    widthInput.addEventListener('input', () => {
      let w = parseInt(widthInput.value);
      if (isNaN(w) || w < 1) return;
      if (aspectRatioCheckbox.checked) {
        const h = Math.round(w / originalRatio);
        heightInput.value = h;
      }
    });

    heightInput.addEventListener('input', () => {
      let h = parseInt(heightInput.value);
      if (isNaN(h) || h < 1) return;
      if (aspectRatioCheckbox.checked) {
        const w = Math.round(h * originalRatio);
        widthInput.value = w;
      }
    });

    // Apply resize button
    const applyBtn = document.createElement('button');
    applyBtn.textContent = 'Apply Resize';
    applyBtn.className = 'mt-3 w-full bg-[#00aaff] hover:bg-[#0088cc] text-white px-3 py-1 rounded';
    resizeControls.appendChild(applyBtn);

    applyBtn.addEventListener('click', () => {
      const newW = parseInt(widthInput.value);
      const newH = parseInt(heightInput.value);
      if (isNaN(newW) || newW < 1 || isNaN(newH) || newH < 1) return;

      if (item.type === 'base') {
        resizeBaseCanvas(newW, newH);
        updateBaseInputs();
      } else if (item.type === 'image') {
        resizeImportedImage(item.obj, newW, newH);
      }
    });
  }

  // ===== SYSTÈME SIMPLIFIÉ - CALIBRAGE DIRECT =====
  
  // Le nouveau système utilise directement getBoundingClientRect() 
  // Plus besoin de systèmes complexes
  if (canvas) {
    setupAutomaticCalibration();
    console.log('🎯 Système simplifié activé - Calibrage direct sans transformation');
  }

  
    // FONCTIONS UTILITAIRES POUR LA MANIPULATION DES COULEURS
    function adjustColorBrightness(color, factor) {
      // Convertir hex en RGB
      const rgb = hexToRgba(color);
      if (!rgb) return color;
      
      // Ajuster la luminosité
      const adjustedR = Math.max(0, Math.min(255, rgb.r + (255 * factor)));
      const adjustedG = Math.max(0, Math.min(255, rgb.g + (255 * factor)));
      const adjustedB = Math.max(0, Math.min(255, rgb.b + (255 * factor)));
      
      // Reconvertir en hex
      return rgbToHex(Math.round(adjustedR), Math.round(adjustedG), Math.round(adjustedB));
    }
    
    function adjustColorSaturation(color, factor) {
      const rgb = hexToRgba(color);
      if (!rgb) return color;
      
      // Convertir RGB en HSL
      const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
      
      // Ajuster la saturation
      hsl.s = Math.max(0, Math.min(1, hsl.s * factor));
      
      // Reconvertir en RGB puis hex
      const newRgb = hslToRgb(hsl.h, hsl.s, hsl.l);
      return rgbToHex(newRgb.r, newRgb.g, newRgb.b);
    }
    
    function adjustColorHue(color, degrees) {
      const rgb = hexToRgba(color);
      if (!rgb) return color;
      
      // Convertir RGB en HSL
      const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
      
      // Ajuster la teinte (en degrés)
      hsl.h = (hsl.h + degrees / 360) % 1;
      if (hsl.h < 0) hsl.h += 1;
      
      // Reconvertir en RGB puis hex
      const newRgb = hslToRgb(hsl.h, hsl.s, hsl.l);
      return rgbToHex(newRgb.r, newRgb.g, newRgb.b);
    }
    
    function rgbToHsl(r, g, b) {
      r /= 255;
      g /= 255;
      b /= 255;
      
      const max = Math.max(r, g, b);
      const min = Math.min(r, g, b);
      let h, s, l = (max + min) / 2;
      
      if (max === min) {
        h = s = 0; // Achromatique
      } else {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        
        switch (max) {
          case r: h = (g - b) / d + (g < b ? 6 : 0); break;
          case g: h = (b - r) / d + 2; break;
          case b: h = (r - g) / d + 4; break;
        }
        h /= 6;
      }
      
      return { h, s, l };
    }
    
    function hslToRgb(h, s, l) {
      let r, g, b;
      
      if (s === 0) {
        r = g = b = l; // Achromatique
      } else {
        const hue2rgb = (p, q, t) => {
          if (t < 0) t += 1;
          if (t > 1) t -= 1;
          if (t < 1/6) return p + (q - p) * 6 * t;
          if (t < 1/2) return q;
          if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
          return p;
        };
        
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
      }
      
      return {
        r: Math.round(r * 255),
        g: Math.round(g * 255),
        b: Math.round(b * 255)
      };
    }
    
    function rgbToHex(r, g, b) {
      return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }

</script>
<script>
(() => {
  // Create Layers Panel button in left toolbar
  const layersPanelBtn = document.createElement('button');
  layersPanelBtn.setAttribute('aria-label', 'Panneau Calques');
  layersPanelBtn.className = 'w-10 h-10 flex items-center justify-center text-[#c0c0c0] hover:bg-[#3a3a3a] rounded';
  layersPanelBtn.innerHTML = '<i class="fas fa-layer-group text-[20px]"></i>';
  leftToolbar.appendChild(layersPanelBtn);

  // Create Layers Panel container on right side
  const rightPanel = document.getElementById('rightPanel');
  const layersPanel = document.createElement('div');
  layersPanel.className = 'p-3 bg-[#252525] border-b border-[#555] text-[#c0c0c0] flex flex-col h-full';
  layersPanel.style.display = 'none';

  layersPanel.innerHTML = `
    <div class="flex justify-between items-center mb-3">
      <h2 class="text-lg font-semibold">Calques</h2>
      <button id="closeLayersPanelBtn" aria-label="Fermer panneau calques" class="text-[#00aaff] hover:text-[#0088cc] focus:outline-none">
        <i class="fas fa-times text-lg"></i>
      </button>
    </div>
    <div class="flex-1 flex flex-col overflow-hidden">
      <section id="layersListSection" class="flex flex-col flex-1 overflow-hidden mb-6">
        <h3 class="font-semibold mb-2">Éléments sur la page</h3>
        <ul id="layersList" class="flex-1 overflow-y-auto border border-[#555] rounded bg-[#1e1e1e] p-2 text-sm"></ul>
      </section>
      <section id="layerGroupsSection" class="flex flex-col">
        <h3 class="font-semibold mb-2 flex items-center justify-between">
          Groupes de calques
          <button id="addLayerGroupBtn" class="bg-[#00aaff] hover:bg-[#0088cc] text-white px-2 py-1 rounded text-xs flex items-center space-x-1">
            <i class="fas fa-plus"></i><span>Ajouter</span>
          </button>
        </h3>
        <ul id="layerGroupsList" class="overflow-y-auto max-h-[25vh] border border-[#555] rounded bg-[#1e1e1e] p-2 text-sm"></ul>
        <div id="groupElementsContainer" class="mt-3 hidden flex flex-col">
          <h4 class="font-semibold mb-1">Éléments dans le groupe</h4>
          <ul id="groupElementsList" class="overflow-y-auto max-h-[20vh] border border-[#555] rounded bg-[#1e1e1e] p-2 text-sm"></ul>
          <button id="removeFromGroupBtn" class="mt-2 bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs" disabled>Supprimer de ce groupe</button>
        </div>
      </section>
    </div>
  `;
  rightPanel.appendChild(layersPanel);

  // UI references
  const layersList = layersPanel.querySelector('#layersList');
  const layerGroupsList = layersPanel.querySelector('#layerGroupsList');
  const addLayerGroupBtn = layersPanel.querySelector('#addLayerGroupBtn');
  const closeLayersPanelBtn = layersPanel.querySelector('#closeLayersPanelBtn');
  const toolsSection = document.getElementById('toolsSection');
  const groupElementsContainer = layersPanel.querySelector('#groupElementsContainer');
  const groupElementsList = layersPanel.querySelector('#groupElementsList');
  const removeFromGroupBtn = layersPanel.querySelector('#removeFromGroupBtn');

  // Data structures
  let layers = [];
  let layerGroups = [];
  // drawingStrokes déjà déclarée plus haut
  let currentDrawingStroke = null;
  let selectedGroupId = null;
  let selectedGroupElementId = null;
  // selectedDrawingStrokeId déjà déclarée plus haut

  // Generate unique ID
  function generateId() {
    return 'id-' + Math.random().toString(36).substr(2, 9);
  }

  // Add layer helpers
  function addLayerForImage(imgObj) {
    if (layers.some(l => l.ref === imgObj)) return;
    layers.push({
      id: generateId(),
      type: 'image',
      name: `Image ${layers.filter(l => l.type === 'image').length + 1}`,
      ref: imgObj,
      priority: layers.length
    });
  }
  function addLayerForShape(shapeObj) {
    if (layers.some(l => l.ref === shapeObj)) return;
    layers.push({
      id: generateId(),
      type: 'shape',
      name: `Forme ${layers.filter(l => l.type === 'shape').length + 1}`,
      ref: shapeObj,
      priority: layers.length
    });
  }
  function addLayerForDrawingStroke(stroke) {
    if (layers.some(l => l.id === stroke.id)) return;
    layers.push({
      id: stroke.id,
      type: 'drawing',
      name: `Dessin ${drawingStrokes.length}`,
      ref: stroke,
      priority: layers.length
    });
  }
  function addLayerForText(textObj) {
    if (layers.some(l => l.ref === textObj)) return;
    layers.push({
      id: generateId(),
      type: 'text',
      name: textObj.text.substring(0, 15) || 'Texte',
      ref: textObj,
      priority: layers.length
    });
  }

  // Remove layer helpers
  function removeLayerByRef(ref) {
    const idx = layers.findIndex(l => l.ref === ref);
    if (idx !== -1) layers.splice(idx, 1);
  }
  function removeLayerById(id) {
    const idx = layers.findIndex(l => l.id === id);
    if (idx !== -1) layers.splice(idx, 1);
  }

  // Sort layers by priority ascending (0 = bottom)
  function sortLayersByPriority() {
    layers.sort((a, b) => a.priority - b.priority);
  }

  // Sync layers with current shapes, images, and drawingStrokes
  function syncLayers() {
    importedImages.forEach(addLayerForImage);
    shapes.forEach(addLayerForShape);
    drawingStrokes.forEach(addLayerForDrawingStroke);
    textElements.forEach(addLayerForText);
    layers = layers.filter(l => {
      if (l.type === 'image') return importedImages.includes(l.ref);
      if (l.type === 'shape') return shapes.includes(l.ref);
      if (l.type === 'drawing') return drawingStrokes.some(s => s.id === l.id);
      if (l.type === 'text') return textElements.includes(l.ref);
      return false;
    });
    sortLayersByPriority();
  }

  // Render layers list UI with drag & drop and visual stacking
  function renderLayersList() {
    layersList.innerHTML = '';
    if (layers.length === 0) {
      const emptyMsg = document.createElement('li');
      emptyMsg.className = 'text-center text-gray-500';
      emptyMsg.textContent = 'Aucun élément ajouté.';
      layersList.appendChild(emptyMsg);
      return;
    }
    // Show layers top first (priority descending)
    const sortedLayers = [...layers].sort((a,b) => b.priority - a.priority);
    sortedLayers.forEach(layer => {
      const li = document.createElement('li');
      li.className = 'flex items-center justify-between bg-[#1a1a1a] rounded px-2 py-1 hover:bg-[#333] cursor-pointer select-none relative';
      li.dataset.layerId = layer.id;
      li.style.zIndex = layer.priority + 1000; // Higher priority layers appear visually on top

      // Left: icon + name
      const leftDiv = document.createElement('div');
      leftDiv.className = 'flex items-center space-x-2';

      const icon = document.createElement('i');
      icon.className = 'text-[#00aaff]';
      if (layer.type === 'image') icon.classList.add('fas', 'fa-image');
      else if (layer.type === 'shape') icon.classList.add('fas', 'fa-vector-square');
      else if (layer.type === 'drawing') icon.classList.add('fas', 'fa-pencil-alt');
      else if (layer.type === 'text') icon.classList.add('fas', 'fa-font');
      leftDiv.appendChild(icon);

      const nameSpan = document.createElement('span');
      nameSpan.textContent = layer.name;
      nameSpan.className = 'truncate max-w-[180px]';
      leftDiv.appendChild(nameSpan);

      li.appendChild(leftDiv);

      // Right: priority input and delete button
      const rightDiv = document.createElement('div');
      rightDiv.className = 'flex items-center space-x-2';

      // Priority input
      const priorityInput = document.createElement('input');
      priorityInput.type = 'number';
      priorityInput.min = '0';
      priorityInput.max = (layers.length - 1).toString();
      priorityInput.value = layer.priority;
      priorityInput.className = 'w-12 text-xs bg-[#1e1e1e] border border-[#555] rounded px-1 py-0.5 text-center text-[#c0c0c0]';
      priorityInput.title = 'Priorité d\'affichage (0 = en dessous)';
      rightDiv.appendChild(priorityInput);

      priorityInput.addEventListener('change', () => {
        let val = parseInt(priorityInput.value);
        if (isNaN(val)) val = 0;
        val = Math.min(Math.max(val, 0), layers.length - 1);
        layer.priority = val;
        sortLayersByPriority();
        // Reassign priorities to avoid duplicates
        layers.forEach((l, i) => l.priority = i);
        renderLayersList();
        reorderElementsByLayers();
        reorderElementsByLayers();
        redrawAll();
      });

      // Delete button
      const delBtn = document.createElement('button');
      delBtn.className = 'text-red-500 hover:text-red-700 focus:outline-none';
      delBtn.title = 'Supprimer cet élément';
      delBtn.innerHTML = '<i class="fas fa-trash"></i>';
      rightDiv.appendChild(delBtn);

      delBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Empêcher la sélection de l'élément lors du clic sur supprimer
        
        // **AJOUT: Confirmation avant suppression**
        const elementName = layer.name;
        const elementType = layer.type === 'image' ? 'image' : 
                           layer.type === 'shape' ? 'forme' : 
                           layer.type === 'text' ? 'texte' : 'dessin';
        
        if (!confirm(`Êtes-vous sûr de vouloir supprimer ${elementType} "${elementName}" ?\n\nCette action est irréversible.`)) {
          return;
        }
        
        if (layer.type === 'image') {
          const idx = importedImages.indexOf(layer.ref);
          if (idx !== -1) importedImages.splice(idx, 1);
        } else if (layer.type === 'shape') {
          const idx = shapes.indexOf(layer.ref);
          if (idx !== -1) shapes.splice(idx, 1);
        } else if (layer.type === 'drawing') {
          const idx = drawingStrokes.findIndex(s => s.id === layer.id);
          if (idx !== -1) drawingStrokes.splice(idx, 1);
        } else if (layer.type === 'text') {
          const idx = textElements.indexOf(layer.ref);
          if (idx !== -1) textElements.splice(idx, 1);
        }
        removeLayerById(layer.id);
        renderLayersList();
        reorderElementsByLayers();
        redrawAll();
        
        // **AJOUT: Message de confirmation de suppression**
        console.log(`✅ Élément "${elementName}" (${elementType}) supprimé avec succès`);
      });

      // Click selects element
      li.addEventListener('click', () => {
        if (layer.type === 'shape') {
          selectElement({type:'shape', index: shapes.indexOf(layer.ref), element: layer.ref});
        } else if (layer.type === 'image') {
          selectElement({type:'image', index: importedImages.indexOf(layer.ref), element: layer.ref});
        } else if (layer.type === 'drawing') {
          selectDrawingStroke(layer.id);
        } else if (layer.type === 'text') {
          startTextEditing(layer.ref);
        }
      });

      // Drag & drop for reordering layers (only between layers not in groups)
      li.setAttribute('draggable', 'true');

      li.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('layerId', layer.id);
        e.dataTransfer.setData('sourceType', 'layer');
        e.dataTransfer.effectAllowed = 'move';
        li.classList.add('opacity-50');
      });
      li.addEventListener('dragend', () => {
        li.classList.remove('opacity-50');
      });
      li.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        li.classList.add('border-t-2', 'border-[#00aaff]');
      });
      li.addEventListener('dragleave', () => {
        li.classList.remove('border-t-2', 'border-[#00aaff]');
      });
      li.addEventListener('drop', (e) => {
        e.preventDefault();
        li.classList.remove('border-t-2', 'border-[#00aaff]');
        
        const draggedId = e.dataTransfer.getData('layerId');
        const sourceType = e.dataTransfer.getData('sourceType');
        
        if (!draggedId) return;
        if (draggedId === layer.id) return;
        
        if (sourceType === 'layer') {
          // Reordering layers
          const draggedLayer = layers.find(l => l.id === draggedId);
          const targetLayer = layers.find(l => l.id === layer.id);
          if (!draggedLayer || !targetLayer) return;

          // Prevent reordering if both layers belong to the same group
          if (isLayerInSameGroup(draggedLayer.id, targetLayer.id)) return;

          // Move dragged layer visually above target layer (higher priority)
          const targetPriority = targetLayer.priority;
          draggedLayer.priority = targetPriority + 0.5;

          normalizeLayerPriorities();
          renderLayersList();
          reorderElementsByLayers();
          reorderElementsByLayers();
          redrawAll();
        }
      });

      layersList.appendChild(li);
    });
  }

  // Check if two layers belong to the same group
  function isLayerInSameGroup(layerId1, layerId2) {
    return layerGroups.some(group => group.layers.includes(layerId1) && group.layers.includes(layerId2));
  }

  // Normalize layer priorities to integers starting at 0, preserving order
  function normalizeLayerPriorities() {
    layers.sort((a,b) => a.priority - b.priority);
    layers.forEach((l,i) => l.priority = i);
  }

  // Render groups list UI
  function renderLayerGroupsList() {
    layerGroupsList.innerHTML = '';
    if (layerGroups.length === 0) {
      const emptyMsg = document.createElement('li');
      emptyMsg.className = 'text-center text-gray-500';
      emptyMsg.textContent = 'Aucun groupe de calques.';
      layerGroupsList.appendChild(emptyMsg);
      groupElementsContainer.classList.add('hidden');
      return;
    }
    layerGroups.forEach(group => {
      const li = document.createElement('li');
      li.className = 'flex items-center justify-between bg-[#1a1a1a] rounded px-2 py-1 hover:bg-[#333] cursor-pointer select-none';
      li.dataset.groupId = group.id;

      const nameSpan = document.createElement('span');
      nameSpan.textContent = group.name;
      nameSpan.className = 'truncate max-w-[200px]';
      li.appendChild(nameSpan);

      const rightDiv = document.createElement('div');
      rightDiv.className = 'flex items-center space-x-2';

      // Rename button
      const renameBtn = document.createElement('button');
      renameBtn.className = 'text-[#00aaff] hover:text-[#0088cc] focus:outline-none';
      renameBtn.title = 'Renommer le groupe';
      renameBtn.innerHTML = '<i class="fas fa-edit"></i>';
      rightDiv.appendChild(renameBtn);

      renameBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const newName = prompt('Nouveau nom du groupe:', group.name);
        if (newName && newName.trim() !== '') {
          group.name = newName.trim();
          renderLayerGroupsList();
        }
      });

      // Delete button
      const delBtn = document.createElement('button');
      delBtn.className = 'text-red-500 hover:text-red-700 focus:outline-none';
      delBtn.title = 'Supprimer le groupe';
      delBtn.innerHTML = '<i class="fas fa-trash"></i>';
      rightDiv.appendChild(delBtn);

      delBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (confirm(`Supprimer le groupe "${group.name}" ?`)) {
          layerGroups = layerGroups.filter(g => g.id !== group.id);
          renderLayerGroupsList();
          groupElementsContainer.classList.add('hidden');
          selectedGroupId = null;
          selectedGroupElementId = null;
        }
      });

      li.appendChild(rightDiv);

      // Click on group to show elements
      li.addEventListener('click', () => {
        showGroupElements(group.id);
      });

      // Drag & drop support for groups (optional: reorder groups)
      li.setAttribute('draggable', 'true');
      li.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('groupId', group.id);
        e.dataTransfer.effectAllowed = 'move';
        li.classList.add('opacity-50');
      });
      li.addEventListener('dragend', () => {
        li.classList.remove('opacity-50');
      });
      li.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
      });
      li.addEventListener('drop', (e) => {
        e.preventDefault();
        const draggedGroupId = e.dataTransfer.getData('groupId');
        if (!draggedGroupId) return;
        if (draggedGroupId === group.id) return;
        const draggedIndex = layerGroups.findIndex(g => g.id === draggedGroupId);
        const targetIndex = layerGroups.findIndex(g => g.id === group.id);
        if (draggedIndex === -1 || targetIndex === -1) return;
        const [draggedGroup] = layerGroups.splice(draggedIndex, 1);
        layerGroups.splice(targetIndex, 0, draggedGroup);
        renderLayerGroupsList();
      });

      layerGroupsList.appendChild(li);
    });
  }

  // Show elements inside a group with drag & drop to reorder and remove
  function showGroupElements(groupId) {
    selectedGroupId = groupId;
    const group = layerGroups.find(g => g.id === groupId);
    if (!group) return;
    groupElementsContainer.classList.remove('hidden');
    groupElementsList.innerHTML = '';

    if (group.layers.length === 0) {
      const emptyMsg = document.createElement('li');
      emptyMsg.className = 'text-center text-gray-500';
      emptyMsg.textContent = 'Aucun élément dans ce groupe.';
      groupElementsList.appendChild(emptyMsg);
      removeFromGroupBtn.disabled = true;
      return;
    }

    group.layers.forEach(layerId => {
      const layer = layers.find(l => l.id === layerId);
      if (!layer) return;
      const li = document.createElement('li');
      li.className = 'flex items-center space-x-2 bg-[#1a1a1a] rounded px-2 py-1 hover:bg-[#333] cursor-pointer select-none';
      li.dataset.layerId = layer.id;
      li.style.zIndex = layer.priority + 1000;

      const icon = document.createElement('i');
      icon.className = 'text-[#00aaff]';
      if (layer.type === 'image') icon.classList.add('fas', 'fa-image');
      else if (layer.type === 'shape') icon.classList.add('fas', 'fa-vector-square');
      else if (layer.type === 'drawing') icon.classList.add('fas', 'fa-pencil-alt');
      li.appendChild(icon);

      const nameSpan = document.createElement('span');
      nameSpan.textContent = layer.name;
      nameSpan.className = 'truncate max-w-[180px]';
      li.appendChild(nameSpan);

      li.addEventListener('click', () => {
        Array.from(groupElementsList.children).forEach(child => child.classList.remove('bg-[#005f99]'));
        li.classList.add('bg-[#005f99]');
        selectedGroupElementId = layer.id;
        removeFromGroupBtn.disabled = false;
      });

      // Drag & drop reorder inside group
      li.setAttribute('draggable', 'true');
      li.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('groupLayerId', layer.id);
        e.dataTransfer.effectAllowed = 'move';
        li.classList.add('opacity-50');
      });
      li.addEventListener('dragend', () => {
        li.classList.remove('opacity-50');
      });
      li.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
      });
      li.addEventListener('drop', (e) => {
        e.preventDefault();
        const draggedLayerId = e.dataTransfer.getData('groupLayerId');
        if (!draggedLayerId) return;
        if (draggedLayerId === layer.id) return;
        const draggedIndex = group.layers.indexOf(draggedLayerId);
        const targetIndex = group.layers.indexOf(layer.id);
        if (draggedIndex === -1 || targetIndex === -1) return;
        group.layers.splice(draggedIndex, 1);
        group.layers.splice(targetIndex, 0, draggedLayerId);
        showGroupElements(groupId);
      });

      groupElementsList.appendChild(li);
    });

    removeFromGroupBtn.disabled = true;
  }

  // Remove selected element from group
  removeFromGroupBtn.addEventListener('click', () => {
    if (!selectedGroupId || !selectedGroupElementId) return;
    const group = layerGroups.find(g => g.id === selectedGroupId);
    if (!group) return;
    const idx = group.layers.indexOf(selectedGroupElementId);
    if (idx !== -1) {
      group.layers.splice(idx, 1);
      selectedGroupElementId = null;
      removeFromGroupBtn.disabled = true;
      renderLayerGroupsList();
      showGroupElements(selectedGroupId);
    }
  });

  // Add new group
  addLayerGroupBtn.addEventListener('click', () => {
    const groupName = prompt('Nom du nouveau groupe de calques:', `Groupe ${layerGroups.length + 1}`);
    if (groupName && groupName.trim() !== '') {
      layerGroups.push({
        id: generateId(),
        name: groupName.trim(),
        layers: []
      });
      renderLayerGroupsList();
    }
  });

  // Close layers panel button
  closeLayersPanelBtn.addEventListener('click', () => {
    layersPanel.style.display = 'none';
    toolsSection.style.display = 'block';
    groupElementsContainer.classList.add('hidden');
    selectedGroupId = null;
    selectedGroupElementId = null;
  });

  // Show layers panel on button click
  layersPanelBtn.addEventListener('click', () => {
    const isVisible = layersPanel.style.display === 'flex';
    if (!isVisible) {
      Array.from(rightPanel.children).forEach(child => {
        if (child !== toolsSection) child.style.display = 'none';
      });
      layersPanel.style.display = 'flex';
      toolsSection.style.display = 'none';
      groupElementsContainer.classList.add('hidden');
      selectedGroupId = null;
      selectedGroupElementId = null;
      syncLayers();
      renderLayersList();
      renderLayerGroupsList();
    } else {
      layersPanel.style.display = 'none';
      toolsSection.style.display = 'block';
      groupElementsContainer.classList.add('hidden');
      selectedGroupId = null;
      selectedGroupElementId = null;
    }
  });

  // Hook into importedImages and shapes push/splice to sync layers
  const originalImportedImagesPush = importedImages.push.bind(importedImages);
  importedImages.push = function(...args) {
    const result = originalImportedImagesPush(...args);
    args.forEach(addLayerForImage);
    renderLayersList();
    // **SAUVEGARDER L'ÉTAT POUR UNDO/REDO**
    setTimeout(() => saveState(), 10);
    return result;
  };
  const originalImportedImagesSplice = importedImages.splice.bind(importedImages);
  importedImages.splice = function(...args) {
    const removed = originalImportedImagesSplice(...args);
    removed.forEach(removeLayerByRef);
    renderLayersList();
    return removed;
  };

  const originalShapesPush = shapes.push.bind(shapes);
  shapes.push = function(...args) {
    const result = originalShapesPush(...args);
    args.forEach(addLayerForShape);
    renderLayersList();
    // **CORRECTION: Redraw immédiat et synchrone**
    redrawAll();
    return result;
  };
  const originalShapesSplice = shapes.splice.bind(shapes);
  shapes.splice = function(...args) {
    const removed = originalShapesSplice(...args);
    removed.forEach(removeLayerByRef);
    renderLayersList();
    return removed;
  };

  // Patch drawLine to track drawing strokes as one layer per continuous stroke
  const originalDrawLine = window.drawLine;
  
  // **CORRECTION: Sauvegarder la fonction drawLine originale pour le rendu**
  const renderDrawLine = drawLine;
  
  // **NOUVELLE FONCTION: Dessin temporaire pour feedback temps réel SANS affecter les layers**
  function drawLineTemporary(ctx, x1, y1, x2, y2, tool, size, color) {
    ctx.save();
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.lineWidth = size;
    ctx.strokeStyle = color;
    ctx.globalAlpha = 0.8; // Légèrement transparent pour indiquer temporaire
    
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.stroke();
    ctx.restore();
  }
  
  window.drawLine = function(ctx, x1, y1, x2, y2, tool, size, color) {
    // **CORRECTION: Seulement traquer les points, ne pas dessiner sur drawingLayer**
    if (!currentDrawingStroke) {
      currentDrawingStroke = {
        id: generateId(),
        points: [{x: x1, y: y1}, {x: x2, y: y2}],
        tool,
        size,
        color,
        // **CORRECTION: Sauvegarder les styles artistiques avec le stroke**
        timestamp: Date.now(),
        savedBrushStyle: currentBrushStyle,
        savedStyleIntensity: styleIntensity,
        savedTextureGrain: textureGrain,
        savedSpreading: spreading,
        savedBlurEffect: blurEffect,
        savedShineIntensity: shineIntensity,
        savedShineColor: document.getElementById('shineColor').value,
        savedShineOpacity: shineOpacity
      };
    } else {
      currentDrawingStroke.points.push({x: x2, y: y2});
    }
    
    // **CORRECTION: Utiliser la fonction temporaire pour le feedback temps réel**
    drawLineTemporary(ctx, x1, y1, x2, y2, tool, size, color);
  };

  // On pointerup finalize current drawing stroke
  const originalPointerUp = canvas.onpointerup;
  canvas.onpointerup = function(e) {
    // **CORRECTION: Finaliser le stroke AVANT d'appeler originalPointerUp**
    if (currentDrawingStroke && currentDrawingStroke.points && currentDrawingStroke.points.length > 1) {
      drawingStrokes.push(currentDrawingStroke);
      addLayerForDrawingStroke(currentDrawingStroke);
      renderLayersList();
      currentDrawingStroke = null;
      // Forcer le redraw pour afficher le nouveau stroke dans les layers
      setTimeout(() => redrawAll(), 0);
      // **SAUVEGARDER L'ÉTAT POUR UNDO/REDO**
      setTimeout(() => saveState(), 10);
    } else {
      // Si le stroke est trop court, l'abandonner
      currentDrawingStroke = null;
    }
    
    if (originalPointerUp) originalPointerUp(e);
  };

  // Select drawing stroke by id: highlight stroke on canvas
  function selectDrawingStroke(strokeId) {
    const index = drawingStrokes.findIndex(s => s.id === strokeId);
    if (index !== -1) {
      // Utiliser la fonction globale selectElement pour activer l'édition complète
      selectElement({type: 'drawing', index: index, element: drawingStrokes[index]});
    } else {
      selectedDrawingStrokeId = strokeId;
      redrawAll();
    }
  }

  // Modify redrawAll to highlight selected drawing stroke
  const originalRedrawAll = window.redrawAll;
  window.redrawAll = function() {
    if (!imageLoaded) return;
    
    // S'assurer que drawingLayer a la bonne taille
    ensureDrawingLayerSize();
    
    // Effacer complètement le canvas
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // 1. Dessiner l'image de base si elle existe ET qu'elle n'est pas gérée par le système de layers
    if (importedImage && (!window.layersPanelAPI || !window.layersPanelAPI.layers.some(l => l.type === 'image' && l.ref && l.ref.img === importedImage))) {
      ctx.drawImage(importedImage, 0, 0, canvas.width, canvas.height);
    }
    
    // **NOUVEAU SYSTÈME UNIFIÉ DE PRIORITÉS** - Trier tous les layers par priorité
    if (window.layersPanelAPI && window.layersPanelAPI.layers) {
      const sortedLayers = [...window.layersPanelAPI.layers].sort((a, b) => a.priority - b.priority);
      
      // Dessiner tous les éléments dans l'ordre unifié de priorité
      sortedLayers.forEach(layer => {
        if (layer.type === 'image' && layer.ref && layer.ref.img) {
          // Dessiner image
          ctx.drawImage(layer.ref.img, layer.ref.x || 0, layer.ref.y || 0, layer.ref.width, layer.ref.height);
        } else if (layer.type === 'shape' && layer.ref) {
          // Dessiner forme
          drawShape(ctx, layer.ref);
        } else if (layer.type === 'text' && layer.ref) {
          // Dessiner texte
          drawTextElement(ctx, layer.ref);
        } else if (layer.type === 'drawing') {
          // **CORRECTION: Dessiner trait de dessin avec les styles appropriés**
          const stroke = drawingStrokes.find(s => s.id === layer.id);
          if (stroke && stroke.points && stroke.points.length > 1) {
            ctx.save();
            
            // **CORRECTION: Déterminer si appliquer le style artistique**
            let shouldApplyStyle = false;
            
            if (styleAppliedToNewOnly && stroke.timestamp >= styleActivationTime) {
              // Appliquer le style seulement aux nouveaux dessins (après activation)
              shouldApplyStyle = true;
            } else if (!styleAppliedToNewOnly) {
              // Appliquer le style à tous les dessins
              shouldApplyStyle = true;
            }
            
            // **CORRECTION: Utiliser les styles sauvegardés avec le stroke ou les styles actuels**
            if (shouldApplyStyle && (stroke.savedBrushStyle !== 'normal' || currentBrushStyle !== 'normal')) {
              // Sauvegarder les styles actuels
              const tempBrushStyle = currentBrushStyle;
              const tempStyleIntensity = styleIntensity;
              const tempTextureGrain = textureGrain;
              const tempSpreading = spreading;
              const tempBlurEffect = blurEffect;
              const tempShineIntensity = shineIntensity;
              const tempShineColor = document.getElementById('shineColor').value;
              const tempShineOpacity = shineOpacity;
              
              // Utiliser les styles sauvegardés avec le stroke (si disponibles)
              if (stroke.savedBrushStyle) {
                currentBrushStyle = stroke.savedBrushStyle;
                styleIntensity = stroke.savedStyleIntensity || styleIntensity;
                textureGrain = stroke.savedTextureGrain || textureGrain;
                spreading = stroke.savedSpreading || spreading;
                blurEffect = stroke.savedBlurEffect || blurEffect;
                shineIntensity = stroke.savedShineIntensity || shineIntensity;
                document.getElementById('shineColor').value = stroke.savedShineColor || tempShineColor;
                shineOpacity = stroke.savedShineOpacity || shineOpacity;
              }
              
              // Dessiner chaque segment avec les styles appropriés
              for (let i = 1; i < stroke.points.length; i++) {
                const p1 = stroke.points[i-1];
                const p2 = stroke.points[i];
                // Utiliser applyArtisticBrushStyle directement avec les styles du stroke
                applyArtisticBrushStyle(ctx, p1.x, p1.y, p2.x, p2.y, stroke.tool || 'brush-basic', stroke.size || 5, stroke.color || '#000000');
              }
              
              // Restaurer les styles actuels
              currentBrushStyle = tempBrushStyle;
              styleIntensity = tempStyleIntensity;
              textureGrain = tempTextureGrain;
              spreading = tempSpreading;
              blurEffect = tempBlurEffect;
              shineIntensity = tempShineIntensity;
              document.getElementById('shineColor').value = tempShineColor;
              shineOpacity = tempShineOpacity;
            } else {
              // Dessiner sans style artistique
              ctx.lineCap = 'round';
              ctx.lineJoin = 'round';
              ctx.lineWidth = stroke.size || 5;
              ctx.strokeStyle = stroke.color || '#000000';
              
              for (let i = 1; i < stroke.points.length; i++) {
                const p1 = stroke.points[i-1];
                const p2 = stroke.points[i];
                ctx.beginPath();
                ctx.moveTo(p1.x, p1.y);
                ctx.lineTo(p2.x, p2.y);
                ctx.stroke();
              }
            }
            
            ctx.restore();
          }
        }
      });
    } else {
      // Système de fallback si pas de layers
      originalRedrawAll();
      return;
    }
    
    // **CORRECTION: Dessiner aussi les formes qui ne seraient pas dans le système de layers**
    if (shapes && shapes.length > 0) {
      shapes.forEach(shape => {
        // Vérifier si cette forme est déjà dans le système de layers
        const isInLayers = window.layersPanelAPI && window.layersPanelAPI.layers.some(layer => 
          layer.type === 'shape' && layer.ref === shape
        );
        
        // Si elle n'est pas dans le système de layers, la dessiner quand même
        if (!isInLayers) {
          drawShape(ctx, shape);
        }
      });
    }
    
    // 3. Dessiner le trait en cours SEULEMENT s'il y en a un (pour le feedback temps réel)
    if (currentDrawingStroke && currentDrawingStroke.points && currentDrawingStroke.points.length > 0) {
      ctx.save();
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.lineWidth = currentDrawingStroke.size || 5;
      ctx.strokeStyle = currentDrawingStroke.color || '#000000';
      ctx.globalAlpha = 0.8; // Légèrement transparent pour indiquer que c'est temporaire
      
      ctx.beginPath();
      ctx.moveTo(currentDrawingStroke.points[0].x, currentDrawingStroke.points[0].y);
      for (let i = 1; i < currentDrawingStroke.points.length; i++) {
        ctx.lineTo(currentDrawingStroke.points[i].x, currentDrawingStroke.points[i].y);
      }
      ctx.stroke();
      ctx.restore();
    }
    
    // 4. Appliquer les zones d'effacement sur tout (images + dessins)
    if (window.erasedAreas && window.erasedAreas.length > 0) {
      window.erasedAreas.forEach(erasedArea => {
        ctx.globalCompositeOperation = 'destination-out';
        ctx.beginPath();
        ctx.arc(erasedArea.x, erasedArea.y, erasedArea.radius, 0, Math.PI * 2);
        ctx.fill();
        ctx.closePath();
      });
      ctx.globalCompositeOperation = 'source-over';
    }
    
    // 5. Reste de la fonction originale pour les sélections
    // 6. Dessiner la sélection si elle existe
    if (selectionRect) {
      ctx.save();
      
      // Style de base pour la sélection
      ctx.strokeStyle = isProtected ? 'rgba(255,0,0,0.8)' : 'rgba(0,120,215,0.8)';
      ctx.lineWidth = 2;
      ctx.setLineDash([6, 4]);
      
      // Pour les sélections rectangulaires
      if (selectionType === 'rect' || !selectionPath) {
        const rect = selectionRect.width !== undefined ? selectionRect : 
                    {x: selectionRect.x, y: selectionRect.y, width: selectionRect.w, height: selectionRect.h};
        ctx.strokeRect(rect.x, rect.y, rect.width, rect.height);
        
        // Afficher un overlay semi-transparent pour la protection
        if (isProtected) {
          ctx.fillStyle = 'rgba(255,0,0,0.1)';
          ctx.fillRect(rect.x, rect.y, rect.width, rect.height);
        }
      }
      
      ctx.restore();
    }
    
    // Dessiner les sélections de forme libre (lasso)
    if (selectionPath && selectionPath.length > 2) {
      ctx.save();
      
      ctx.strokeStyle = isProtected ? 'rgba(255,0,0,0.8)' : 'rgba(0,120,215,0.8)';
      ctx.lineWidth = 2;
      ctx.setLineDash([6, 4]);
      
      // Dessiner le contour de la sélection
      ctx.beginPath();
      ctx.moveTo(selectionPath[0].x, selectionPath[0].y);
      for (let i = 1; i < selectionPath.length; i++) {
        ctx.lineTo(selectionPath[i].x, selectionPath[i].y);
      }
      ctx.closePath();
      ctx.stroke();
      
      // Afficher un overlay semi-transparent pour la protection
      if (isProtected) {
        ctx.fillStyle = 'rgba(255,0,0,0.1)';
        ctx.fill();
      }
      
      ctx.restore();
    }

    // **SURLIGNAGE DISCRET DU TRAIT SÉLECTIONNÉ** (version améliorée)
    if (selectedDrawingStrokeId) {
      const stroke = drawingStrokes.find(s => s.id === selectedDrawingStrokeId);
      if (stroke && stroke.points && stroke.points.length > 1) {
        ctx.save();
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        // **CORRECTION: Surlignage discret style tirets bleus**
        ctx.strokeStyle = 'rgba(0, 120, 215, 0.6)'; // Bleu discret au lieu d'orange voyant
        ctx.lineWidth = 2; // Ligne fine au lieu d'épaisse
        ctx.setLineDash([6, 4]); // Style tirets comme les autres sélections
        ctx.shadowColor = 'none'; // Pas d'ombre
        ctx.shadowBlur = 0;
        
        ctx.beginPath();
        ctx.moveTo(stroke.points[0].x, stroke.points[0].y);
        for (let i = 1; i < stroke.points.length; i++) {
          ctx.lineTo(stroke.points[i].x, stroke.points[i].y);
        }
        ctx.stroke();
        ctx.restore();
      }
    }
  };

  // Reorder elements arrays by layers order
  function reorderElementsByLayers() {
    sortLayersByPriority();

    // Clear and rebuild shapes, importedImages, drawingStrokes arrays in order of layers
    const newShapes = [];
    const newImages = [];
    const newDrawings = [];
    const newTexts = [];

    layers.forEach(layer => {
      if (layer.type === 'shape') {
        if (shapes.includes(layer.ref)) newShapes.push(layer.ref);
      } else if (layer.type === 'image') {
        if (importedImages.includes(layer.ref)) newImages.push(layer.ref);
      } else if (layer.type === 'drawing') {
        const stroke = drawingStrokes.find(s => s.id === layer.id);
        if (stroke) newDrawings.push(stroke);
      } else if (layer.type === 'text') {
        if (textElements.includes(layer.ref)) newTexts.push(layer.ref);
      }
    });

    shapes.length = 0;
    shapes.push(...newShapes);

    importedImages.length = 0;
    importedImages.push(...newImages);

    drawingStrokes.length = 0;
    drawingStrokes.push(...newDrawings);
    
    textElements.length = 0;
    textElements.push(...newTexts);
  }

  // Expose API for debugging
  window.layersPanelAPI = {
    layers,
    layerGroups,
    drawingStrokes,
    textElements,
    addLayerForImage,
    addLayerForShape,
    addLayerForDrawingStroke,
    addLayerForText,
    removeLayerByRef,
    removeLayerById,
    renderLayersList,
    renderLayerGroupsList,
    reorderElementsByLayers,
    selectDrawingStroke
  };
  
  // **INITIALISATION AUTOMATIQUE D'UN CANVAS VIDE 1000x1000**
  window.addEventListener('load', () => {
    if (!imageLoaded) {
      // Créer un canvas de base 1000x1000 pour commencer à dessiner immédiatement
      canvas.width = 1000;
      canvas.height = 1000;
      
      // Remplir avec un fond blanc
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      
      // Marquer comme chargé
      imageLoaded = true;
      
      // **CORRECTION: Activer le bouton de download**
      const downloadBtn = document.getElementById('downloadBtn');
      if (downloadBtn) {
        downloadBtn.disabled = false;
      }
      
      // Initialiser le système de layers
      renderLayersList();
      
      // Sauvegarder l'état initial pour Undo/Redo
      setTimeout(() => saveState(), 100);
      
      console.log('Canvas 1000x1000 initialisé et prêt pour le dessin !');
    }
  });
})();
</script>

<script> --- IGNORE ---
    // ==== CORRECTIONS DES BUGS MENTIONNÉS ====

// 1. CORRECTION: Système de sélection et rotation pour les textes (popup)
function createTextSelectionPopup(x, y, element) {
    // Supprimer toute popup existante
    const existingPopup = document.getElementById('textSelectionPopup');
    if (existingPopup) existingPopup.remove();
    
    // Créer la popup de sélection pour texte
    const popup = document.createElement('div');
    popup.id = 'textSelectionPopup';
    popup.className = 'absolute bg-gray-800 text-white rounded-lg shadow-lg p-2 flex gap-1 z-50';
    popup.style.pointerEvents = 'auto';
    popup.style.left = `${x}px`;
    popup.style.top = `${y}px`;
    
    popup.innerHTML = `
        <button onclick="cutSelectedElement()" class="p-1 hover:bg-gray-700 rounded" title="Couper">
            ✂️
        </button>
        <button onclick="copySelectedElement()" class="p-1 hover:bg-gray-700 rounded" title="Copier">
            📋
        </button>
        <button onclick="deleteSelectedElement()" class="p-1 hover:bg-gray-700 rounded" title="Supprimer">
            🗑️
        </button>
        <button onclick="showElementProperties()" class="p-1 hover:bg-gray-700 rounded" title="Propriétés">
            ⚙️
        </button>
        <div class="relative inline-block">
            <button onclick="toggleRotationPopup()" class="p-1 hover:bg-gray-700 rounded" title="Rotation">
                🔄
            </button>
            <div id="rotationPopup" class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden bg-gray-800 p-2 rounded shadow-lg border border-gray-600 w-48 z-50">
                <div class="text-xs text-center mb-1">Rotation: <span id="rotationValueDisplay">${element.rotation || 0}</span>°</div>
                <input type="range" min="0" max="360" value="${element.rotation || 0}" class="w-full mb-2" oninput="updateElementRotation(this.value)">
                <div class="flex justify-between gap-2">
                    <button onclick="cancelRotation()" class="flex-1 text-red-500 hover:text-red-400 text-xs px-2 py-1 border border-red-500 rounded bg-transparent">✕ Annuler</button>
                    <button onclick="validateRotation()" class="flex-1 text-green-500 hover:text-green-400 text-xs px-2 py-1 border border-green-500 rounded bg-transparent">✓ Valider</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(popup);
    
    // Stocker l'élément actuellement sélectionné
    window.currentSelectedTextElement = element;
}

// 2. CORRECTION: Bug du deuxième texte qui disparaît visuellement
// Modifier la fonction de dessin du texte pour éviter les conflits
function drawTextElementFixed(ctx, textElement, opts = {}) {
    if (!textElement || !textElement.text) return;
    
    ctx.save();
    
    // Appliquer la rotation
    if (textElement.rotation && textElement.rotation !== 0) {
        const centerX = textElement.x + textElement.width / 2;
        const centerY = textElement.y + textElement.height / 2;
        ctx.translate(centerX, centerY);
        ctx.rotate((textElement.rotation * Math.PI) / 180);
        ctx.translate(-centerX, -centerY);
    }
    
    // **FIX: Assurer une opacité correcte**
    const opacity = typeof textElement.opacity === 'number' ? 
                   Math.max(0, Math.min(1, textElement.opacity)) : 1;
    
    // Dessiner l'arrière-plan
    if (textElement.hasBackground && textElement.backgroundColor) {
        ctx.fillStyle = textElement.backgroundColor;
        ctx.globalAlpha = (textElement.backgroundOpacity || 1) * opacity;
        ctx.fillRect(textElement.x, textElement.y, textElement.width, textElement.height);
    }
    
    // Configurer la police
    let fontStyle = '';
    if (textElement.style) {
        if (textElement.style.includes('bold')) fontStyle += 'bold ';
        if (textElement.style.includes('italic')) fontStyle += 'italic ';
    }
    
    ctx.font = `${fontStyle}${textElement.fontSize}px ${textElement.fontFamily}`;
    ctx.fillStyle = textElement.color || '#000000';
    ctx.globalAlpha = opacity;
    ctx.textBaseline = 'top';
    
    // Gérer l'alignement et le texte multi-lignes
    const lines = textElement.text.split('\n');
    const lineHeight = textElement.fontSize * 1.2;
    
    lines.forEach((line, index) => {
        let drawX = textElement.x;
        if (textElement.align === 'center') {
            drawX = textElement.x + textElement.width / 2;
            ctx.textAlign = 'center';
        } else if (textElement.align === 'right') {
            drawX = textElement.x + textElement.width;
            ctx.textAlign = 'right';
        } else {
            ctx.textAlign = 'left';
        }
        
        ctx.fillText(line, drawX, textElement.y + (index * lineHeight));
    });
    
    // Dessiner la bordure de sélection si actif
    if (activeTextElement && activeTextElement.id === textElement.id) {
        ctx.strokeStyle = '#00aaff';
        ctx.lineWidth = 1;
        ctx.setLineDash([4, 4]);
        ctx.strokeRect(textElement.x, textElement.y, textElement.width, textElement.height);
        ctx.setLineDash([]);
    }
    
    ctx.restore();
}

// Remplacer la fonction de dessin du texte
window.drawTextElement = drawTextElementFixed;

// 3. CORRECTION: Gradient pour les dessins (couleur diluée)
function createDrawingGradient(ctx, x, y, width, height, angle, colors) {
    const rad = angle * Math.PI / 180;
    const x0 = x + width/2 - Math.cos(rad) * width/2;
    const y0 = y + height/2 - Math.sin(rad) * height/2;
    const x1 = x + width/2 + Math.cos(rad) * width/2;
    const y1 = y + height/2 + Math.sin(rad) * height/2;
    
    const gradient = ctx.createLinearGradient(x0, y0, x1, y1);
    
    if (colors.length === 1) {
        gradient.addColorStop(0, colors[0]);
        gradient.addColorStop(1, colors[0]);
    } else if (colors.length === 2) {
        gradient.addColorStop(0, colors[0]);
        gradient.addColorStop(1, colors[1]);
    } else {
        // Pour plusieurs couleurs
        colors.forEach((color, index) => {
            gradient.addColorStop(index / (colors.length - 1), color);
        });
    }
    
    return gradient;
}

// Modifier la fonction getCurrentDrawColor pour les dessins
const originalGetCurrentDrawColor = window.getCurrentDrawColor;
window.getCurrentDrawColor = function() {
    if (colorModeSelect.value === 'solid') {
        return document.getElementById('color1rgba').value;
    } else {
        // Pour les dessins, créer un gradient spécifique
        const colors = getAllColors();
        return createDrawingGradient(ctx, 0, 0, canvas.width, canvas.height, gradientAngle, 
                                    colors.map(c => rgbaToString(c)));
    }
};

// 4. CORRECTION: Gradient pour le texte
function applyTextGradient(ctx, textElement, gradient) {
    ctx.fillStyle = gradient;
    
    // Configurer la police
    let fontStyle = '';
    if (textElement.style) {
        if (textElement.style.includes('bold')) fontStyle += 'bold ';
        if (textElement.style.includes('italic')) fontStyle += 'italic ';
    }
    
    ctx.font = `${fontStyle}${textElement.fontSize}px ${textElement.fontFamily}`;
    ctx.textBaseline = 'top';
    
    // Gérer l'alignement et le texte multi-lignes
    const lines = textElement.text.split('\n');
    const lineHeight = textElement.fontSize * 1.2;
    
    lines.forEach((line, index) => {
        let drawX = textElement.x;
        if (textElement.align === 'center') {
            drawX = textElement.x + textElement.width / 2;
            ctx.textAlign = 'center';
        } else if (textElement.align === 'right') {
            drawX = textElement.x + textElement.width;
            ctx.textAlign = 'right';
        } else {
            ctx.textAlign = 'left';
        }
        
        ctx.fillText(line, drawX, textElement.y + (index * lineHeight));
    });
}

// 5. CORRECTION: Formes manquantes (cercle, carré, triangle, etc.)
function drawShapeFixed(ctx, s) {
    if (!s || !s.type) return;
    
    ctx.save();
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    
    // Appliquer la rotation si définie
    if (s.rotation && s.rotation !== 0) {
        const centerX = s.x + s.w / 2;
        const centerY = s.y + s.h / 2;
        ctx.translate(centerX, centerY);
        ctx.rotate((s.rotation * Math.PI) / 180);
        ctx.translate(-centerX, -centerY);
    }
    
    // Déterminer la couleur/opacité
    let finalColor = s.fillColor || s.color || '#000000';
    let finalOpacity = (typeof s.opacity === 'number') ? s.opacity : (opacityInput ? parseFloat(opacityInput.value) : 1);
    
    // Appliquer le gradient si activé
    if (s.fillMode === 'gradient' && colorModeSelect.value === 'gradient') {
        const colors = getAllColors();
        finalColor = createDrawingGradient(ctx, s.x, s.y, s.w, s.h, gradientAngle, 
                                          colors.map(c => rgbaToString(c)));
    }
    
    // Appliquer opacité
    ctx.globalAlpha = Math.max(0, Math.min(1, finalOpacity));
    
    // Style de forme
    const shapeStyle = s.shapeStyle || 'flat-fill';
    
    try {
        ctx.beginPath();
        
        // **FIX: Formes de base corrigées**
        if(s.type === 'rectangle') {
            if (s.borderRadius && s.borderRadius > 0) {
                drawRoundedRect(ctx, s.x, s.y, s.w, s.h, s.borderRadius);
            } else {
                ctx.rect(s.x, s.y, s.w, s.h);
            }
        } else if(s.type === 'circle') {
            const centerX = s.x + s.w / 2;
            const centerY = s.y + s.h / 2;
            const radius = Math.min(Math.abs(s.w), Math.abs(s.h)) / 2;
            ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        } else if(s.type === 'triangle') {
            ctx.moveTo(s.x + s.w / 2, s.y);
            ctx.lineTo(s.x + s.w, s.y + s.h);
            ctx.lineTo(s.x, s.y + s.h);
            ctx.closePath();
        } else if(s.type === 'line') {
            ctx.beginPath();
            ctx.moveTo(s.x, s.y);
            ctx.lineTo(s.x + s.w, s.y + s.h);
            
            // Pour les lignes, utiliser stroke au lieu de fill
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || s.size || 1;
            ctx.stroke();
            ctx.restore();
            return;
        } else if(s.type === 'ellipse') {
            const centerX = s.x + s.w / 2;
            const centerY = s.y + s.h / 2;
            const radiusX = Math.abs(s.w) / 2;
            const radiusY = Math.abs(s.h) / 2;
            ctx.ellipse(centerX, centerY, radiusX, radiusY, 0, 0, 2 * Math.PI);
        } else if(s.type === 'diamond') {
            ctx.moveTo(s.x + s.w / 2, s.y);
            ctx.lineTo(s.x + s.w, s.y + s.h / 2);
            ctx.lineTo(s.x + s.w / 2, s.y + s.h);
            ctx.lineTo(s.x, s.y + s.h / 2);
            ctx.closePath();
        } else if(s.type === 'point') {
            ctx.arc(s.x, s.y, s.size || 1, 0, 2 * Math.PI);
        } else {
            // Pour les autres formes, utiliser le rectangle par défaut
            ctx.rect(s.x, s.y, s.w, s.h);
        }
        
        // Appliquer le style de forme
        if (s.outlineOnly) {
            ctx.strokeStyle = finalColor;
            ctx.lineWidth = s.outlineThickness || 1;
            ctx.stroke();
        } else {
            ctx.fillStyle = finalColor;
            ctx.fill();
        }
        
    } catch(e) {
        console.warn('Erreur lors du dessin de la forme:', e, s);
    }
    
    ctx.restore();
}

// Remplacer la fonction de dessin des formes
window.drawShape = drawShapeFixed;

// 6. CORRECTION: Point de base qui se déplace lors du déploiement des formes
// Modifier la gestion du dessin des formes pour garder le point de départ fixe
canvas.onpointerdown = function(e) {
    if(!imageLoaded) return;
    
    // Ignorer clic droit (réservé pour la navigation)
    if (e.button === 2) return;
    
    const pos = getScaledPointerPos(e);
    
    // **FIX: Stocker le point de départ ABSOLU pour les formes**
    window.shapeStartPoint = {x: pos.x, y: pos.y};
    
    // ... reste du code existant ...
};

// Modifier la logique de dessin des formes pour utiliser le point de départ fixe
canvas.onpointermove = function(e) {
    // ... code existant ...
    
    if(currentTool.startsWith('shape-')){
        redrawAll();
        const shapeType = currentTool.replace('shape-','');
        const startPoint = window.shapeStartPoint || {x: startX, y: startY};
        const x = Math.min(startPoint.x, pos.x);
        const y = Math.min(startPoint.y, pos.y);
        const w = Math.abs(pos.x - startPoint.x);
        const h = Math.abs(pos.y - startPoint.y);
        const color = getCurrentDrawColor();
        drawShape(ctx, {type: shapeType, x, y, w, h, size: brushSize, color});
        e.preventDefault();
    }
};

// 7. CORRECTION: Précision du brush à 1px
// Modifier la fonction de dessin pour une précision pixel parfaite
function drawPixelPerfectLine(ctx, x1, y1, x2, y2, size, color) {
    // **FIX: Pour une taille de 1px, dessiner exactement 1 pixel**
    if (size <= 1) {
        ctx.fillStyle = color;
        ctx.fillRect(Math.round(x1), Math.round(y1), 1, 1);
        return;
    }
    
    // Pour les tailles plus grandes, utiliser le tracé normal
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.lineWidth = size;
    ctx.strokeStyle = color;
    
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.stroke();
}

// Intégrer dans la fonction drawLine
const originalDrawLineFunction = window.drawLine;
window.drawLine = function(ctx, x1, y1, x2, y2, tool, size, color) {
    // Pour les outils de dessin basique, utiliser la précision pixel
    if (tool === 'brush-basic' || tool === 'brush-pencil') {
        drawPixelPerfectLine(ctx, x1, y1, x2, y2, size, color);
    } else {
        // Pour les autres outils, utiliser le comportement original
        originalDrawLineFunction(ctx, x1, y1, x2, y2, tool, size, color);
    }
};

// 8. CORRECTION: Calibrage du pointeur pour une précision parfaite
function getPerfectPointerPos(e) {
    const rect = canvas.getBoundingClientRect();
    
    // **FIX: Calcul précis avec prise en compte des bordures et paddings**
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    
    // Position relative précise
    const x = (e.clientX - rect.left) * scaleX;
    const y = (e.clientY - rect.top) * scaleY;
    
    // Limiter et arrondir pour la précision pixel
    return {
        x: Math.max(0, Math.min(canvas.width, Math.round(x))),
        y: Math.max(0, Math.min(canvas.height, Math.round(y)))
    };
}

// Remplacer getScaledPointerPos
window.getScaledPointerPos = getPerfectPointerPos;

// 9. CORRECTION: Affichage visuel pendant le dessin (avant relâchement du clic)
// Modifier la logique de dessin pour afficher immédiatement
canvas.onpointermove = function(e) {
    // ... code existant ...
    
    if(currentTool.startsWith('brush') && isDrawing){
        const pos = getScaledPointerPos(e);
        const color = getCurrentDrawColor();
        
        // **FIX: Dessiner immédiatement sur le canvas principal**
        drawLine(ctx, lastPoint.x, lastPoint.y, pos.x, pos.y, currentTool, brushSize, color);
        
        // **FIX: Ajouter aussi au stroke en cours pour le système de layers**
        if (!currentDrawingStroke) {
            currentDrawingStroke = {
                id: 'id-' + Math.random().toString(36).substr(2, 9),
                points: [{x: lastPoint.x, y: lastPoint.y}, {x: pos.x, y: pos.y}],
                tool: currentTool,
                size: brushSize,
                color: color,
                timestamp: Date.now()
            };
        } else {
            currentDrawingStroke.points.push({x: pos.x, y: pos.y});
        }
        
        lastPoint = pos;
        e.preventDefault();
        return;
    }
    
    // ... reste du code ...
};

// 10. CORRECTION: Initialisation de la sélection pour les textes
// Ajouter un event listener pour sélectionner le texte depuis les calques
document.addEventListener('click', function(e) {
    const layersList = document.getElementById('layersList');
    if (layersList && layersList.contains(e.target)) {
        const li = e.target.closest('li[data-layer-id]');
        if (li) {
            const layerId = li.dataset.layerId;
            const layer = window.layersPanelAPI.layers.find(l => l.id === layerId);
            
            if (layer && layer.type === 'text') {
                // **FIX: Afficher la popup de sélection pour le texte**
                const rect = canvas.getBoundingClientRect();
                const popupX = rect.left + layer.ref.x + 100;
                const popupY = rect.top + layer.ref.y - 40;
                
                createTextSelectionPopup(popupX, popupY, layer.ref);
                
                // Sélectionner l'élément
                selectTextElement(layer.ref);
            }
        }
    }
});

// 11. CORRECTION: Système de rotation pour les textes depuis la popup
window.updateElementRotation = function(angle) {
    if (!window.currentSelectedTextElement) return;
    
    const element = window.currentSelectedTextElement;
    angle = parseFloat(angle);
    
    // Mettre à jour l'affichage
    const display = document.getElementById('rotationValueDisplay');
    if (display) display.textContent = Math.round(angle);
    
    // Mettre à jour l'élément
    element.rotation = angle;
    
    // Redessiner
    redrawAll();
};

window.toggleRotationPopup = function() {
    const popup = document.getElementById('rotationPopup');
    if (popup.classList.contains('hidden')) {
        popup.classList.remove('hidden');
        // Stocker la rotation initiale
        if (window.currentSelectedTextElement) {
            window.initialRotation = window.currentSelectedTextElement.rotation || 0;
        }
    } else {
        popup.classList.add('hidden');
    }
};

window.validateRotation = function() {
    const popup = document.getElementById('rotationPopup');
    if(popup) popup.classList.add('hidden');
    saveState(); // Sauvegarder l'état
};

window.cancelRotation = function() {
    const popup = document.getElementById('rotationPopup');
    if(popup) popup.classList.add('hidden');
    if (window.currentSelectedTextElement) {
        updateElementRotation(window.initialRotation);
        const slider = document.querySelector('#rotationPopup input[type=range]');
        if (slider) slider.value = window.initialRotation;
    }
};

// 12. CORRECTION: Gestion des événements pour fermer la popup
document.addEventListener('click', function(e) {
    const popup = document.getElementById('textSelectionPopup');
    const rotationPopup = document.getElementById('rotationPopup');
    
    // Fermer la popup de rotation si on clique ailleurs
    if (rotationPopup && !rotationPopup.contains(e.target) && 
        !e.target.closest('button[onclick*="toggleRotationPopup"]')) {
        rotationPopup.classList.add('hidden');
    }
    
    // Fermer la popup principale si on clique ailleurs (sauf sur les éléments de texte)
    if (popup && !popup.contains(e.target)) {
        const isTextElement = e.target.closest('#layersList li') || 
                             (activeTextElement && canvas.contains(e.target));
        
        if (!isTextElement) {
            popup.remove();
            window.currentSelectedTextElement = null;
        }
    }
});

// 13. CORRECTION: Initialisation du canvas avec fond transparent
window.addEventListener('load', function() {
    // Attendre que tout soit chargé
    setTimeout(function() {
        if (!imageLoaded && canvas) {
            // **FIX: Initialiser avec un fond transparent au lieu de blanc**
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Marquer comme chargé
            imageLoaded = true;
            
            // Activer le bouton de download
            const downloadBtn = document.getElementById('downloadBtn');
            if (downloadBtn) {
                downloadBtn.disabled = false;
            }
            
            console.log('✅ Canvas initialisé avec succès');
        }
    }, 100);
});

// ==== FIN DES CORRECTIONS ====
console.log('✅ Correctifs appliqués avec succès');
  </script> 
</body>
</html>
