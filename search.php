<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function getAllFiles($dir) {
    $files = [];
    $excludeFiles = [
        'verify.php',
        'sign-up.php',
        'login.php',
        'password-reset.php',
        '404.html',
        '403.html',
        '500.html'
    ];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && 
            in_array($file->getExtension(), ['html', 'php']) && 
            !in_array($file->getBasename(), $excludeFiles)) {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

function extractTitle($content) {
    if (preg_match('/<title>(.*?)<\/title>/i', $content, $matches)) {
        return $matches[1];
    }
    return 'Untitled Page';
}

function getSentences($text) {
    // Split text into sentences using common sentence endings
    $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    return array_map('trim', $sentences);
}

function searchInFiles($searchTerm) {
    $results = [];
    $rootDir = $_SERVER['DOCUMENT_ROOT'];
    $files = getAllFiles($rootDir);
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        
        if (basename($file) === 'search.php') {
            continue;
        }
        
        // Remove navbar content
        $content = preg_replace('/<nav class="navbar".*?<\/nav>/s', '', $content);
        
        $searchableContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
        $searchableContent = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $searchableContent);
        $searchableContent = strip_tags($searchableContent);
        
        if (stripos($searchableContent, $searchTerm) !== false) {
            $sentences = getSentences($searchableContent);
            $matchingSentences = [];
            $position = 0;
            
            foreach ($sentences as $sentence) {
                if (stripos($sentence, $searchTerm) !== false) {
                    $matchingSentences[] = $sentence;
                    $position = stripos($searchableContent, $sentence);
                }
            }
            
            if (!empty($matchingSentences)) {
                $excerpt = implode(' ', array_slice($matchingSentences, 0, 3));
                if (count($matchingSentences) > 3) {
                    $excerpt .= '...';
                }
                $excerpt = preg_replace("/($searchTerm)/i", '<span class="search-highlight">$1</span>', $excerpt);
                
                $title = extractTitle($content);
                $url = str_replace($rootDir, '', $file);
                $url = str_replace('index.html', '', $url);
                $url = str_replace('.html', '', $url);
                $url = ltrim($url, '/');
                
                $results[] = [
                    'title' => $title,
                    'url' => $url . '#search-' . $position,
                    'excerpt' => $excerpt
                ];
            }
        }
    }
    return $results;
}

$searchResults = [];
$searchTerm = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
    $searchTerm = sanitizeInput($_GET['q']);
    if (strlen($searchTerm) < 2) {
        $error = 'Please enter at least 2 characters';
    } else {
        $searchResults = searchInFiles($searchTerm);
    }
}
?>

<!doctypehtml><html lang="en"><head><meta charset="UTF-8"><meta content="width=device-width,initial-scale=1"name="viewport"><title>Search Results</title><link href="/favicon/favicon.ico"rel="icon"type="image/x-icon"><link href="/favicon/favicon-16x16.png"rel="icon"sizes="16x16"type="image/png"><link href="/favicon/favicon-32x32.png"rel="icon"sizes="32x32"type="image/png"><link href="/favicon/apple-touch-icon.png"rel="apple-touch-icon"sizes="180x180"><link href="/favicon/android-chrome-192x192.png"rel="icon"sizes="192x192"><link href="/favicon/android-chrome-512x512.png"rel="icon"sizes="512x512"><link href="/favicon/site.webmanifest"rel="manifest"><link href="all.css"rel="stylesheet"><link href="Lato.css"rel="stylesheet"><link href="main.css"rel="stylesheet"><style>.nav-right .search-container{position:relative;margin-right:1rem}.nav-right .search-input{padding:8px 40px 8px 12px;border:1px solid #ddd;border-radius:4px;font-size:14px;width:200px;transition:all .3s ease}.nav-right .initial-search-btn{background:0 0;border:none;padding:8px;cursor:pointer;color:#fff}.nav-right .search-icons-container{position:absolute;right:8px;top:50%;transform:translateY(-50%);display:flex;align-items:center;gap:8px;pointer-events:none}.nav-right .close-icon,.nav-right .search-btn{background:0 0;border:none;padding:4px;cursor:pointer;color:#666;pointer-events:auto}.nav-right .close-icon:hover,.nav-right .search-btn:hover{color:#333}.nav-right .search-container.active .search-input{display:block}.nav-right .search-container.active .search-icons-container{display:flex}.nav-right .search-container.active .initial-search-btn{display:none}.page-search-container{max-width:800px;margin:0 auto;padding:1rem}.page-search-form{margin-top:100px;background:#fff;padding:2rem;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1)}.page-search-input-wrapper{display:flex;gap:1rem;position:relative}.page-search-input{flex-grow:1;padding:1rem 1.5rem;font-size:1.1rem;border:2px solid #e1e1e1;border-radius:6px;transition:border-color .2s ease}.page-search-input:focus{border-color:#0056b3;outline:0}.page-search-button{padding:1rem 2rem;background-color:#0056b3;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;transition:background-color .2s ease;display:flex;align-items:center;gap:.5rem}.page-search-button:hover{background-color:#004494}.search-results-section{margin-top:2rem}.search-results-count{margin-bottom:1.5rem;color:#666;font-size:1rem;padding:.5rem 0;border-bottom:1px solid #eee}.search-result-card {
    margin-bottom: 2rem;
    padding: 1.5rem;
    border: 1px solid #eee;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
    transition: transform .2s ease;
    cursor: pointer;
    text-decoration: none;
    display: block;
    color: inherit;
}

.search-result-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
}

.search-highlight {
    background-color: #f0d4fc;
    padding: .2rem;
    border-radius: 3px;
}.result-title-link{color:#0056b3;font-size:1.3rem;margin-bottom:.75rem;text-decoration:none;display:block;font-weight:600}.result-title-link:hover{color:#004494;text-decoration:underline}.result-url{color:#006621;font-size:.9rem;margin-bottom:.75rem;word-break:break-all}.result-excerpt{color:#545454;font-size:1rem;line-height:1.7}.search-highlight{background-color:#fff3cd;padding:.2rem 0rem;border-radius:3px;font-weight:500}.search-error{color:#dc3545;padding:1.5rem;background-color:#f8d7da;border-radius:8px;margin-bottom:1.5rem;text-align:center}.no-results-message{text-align:center;padding:3rem 2rem;color:#666;background-color:#f8f9fa;border-radius:8px;margin-top:2rem}.no-results-message i{font-size:3rem;color:#999;margin-bottom:1.5rem;display:block}</style></head><body><nav class="navbar"><div class="container"><a href="home.html"><img alt="Logo"class="logo"src="logo.png"></a><ul class="nav-links"><li class="nav-item"><a href="home.html"class="nav-link"data-translate="home">Home</a></li><li class="nav-item"><a href="getting-started.html"class="nav-link"data-translate="gettingStarted"aria-expanded="false"aria-haspopup="true">Getting Started <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="important-documents.html"class="dropdown-link"data-translate="importantDocuments">Important Documents</a> <a href="housing.html"class="dropdown-link"data-translate="housing">Housing</a> <a href="healthcare.html"class="dropdown-link"data-translate="healthcare">Healthcare</a> <a href="cost-of-living.html"class="dropdown-link"data-translate="costOfLiving">Cost of Living</a></div></li><li class="nav-item"><a href="living-in-the-uae.html"class="nav-link"data-translate="livingInUAE"aria-expanded="false"aria-haspopup="true">Living in the UAE <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="culture-and-customs.html"class="dropdown-link"data-translate="cultureAndCustoms">Culture and Customs</a> <a href="transportation.html"class="dropdown-link"data-translate="transportation">Transportation</a> <a href="shopping.html"class="dropdown-link"data-translate="shopping">Shopping</a></div></li><li class="nav-item"><a href="education.html"class="nav-link"data-translate="education"aria-expanded="false"aria-haspopup="true">Education <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="curriculum-guides.html"class="dropdown-link"data-translate="curriculumGuides">Curriculum Guides</a> <a href="school-listings.html"class="dropdown-link"data-translate="schoolListings">School Listings</a></div></li><li class="nav-item"><a href="working-in-the-uae.html"class="nav-link"data-translate="workingInUAE"aria-expanded="false"aria-haspopup="true">Working in the UAE <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="labour-card.html"class="dropdown-link"data-translate="labourCard">Labour Card</a> <a href="job-market-insights.html"class="dropdown-link"data-translate="jobMarketInsights">Job Market Insights</a> <a href="labour-laws.html"class="dropdown-link"data-translate="labourLaws">Labour Laws</a> <a href="becoming-an-entrepreneur.html"class="dropdown-link"data-translate="becomingEntrepreneur">Becoming an Entrepreneur</a> <a href="networking-oppurtunities.html"class="dropdown-link"data-translate="networkingOpportunities">Networking Opportunities</a></div></li><li class="nav-item"><a href="community-and-support.html"class="nav-link"data-translate="communitySupport"aria-expanded="false"aria-haspopup="true">Community & Support <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="faq.php"class="dropdown-link"data-translate="faq">FAQ</a> <a href="contact-us.php"class="dropdown-link"data-translate="contactUs">Contact Us</a></div></li></ul><div class="nav-right"><div class="search-container"><input class="search-input"name="q"placeholder="Search..."><div class="search-icons-container"><i class="fas fa-times close-icon"id="close-icon"></i> <button class="search-btn"type="button"id="search-btn"><i class="fas fa-search"></i></button></div></div><div class="lang-dropdown"><button class="lang-btn"><i class="fa-globe fal"style="color:#fff"></i><div id="google_translate_element"></div></button></div><a href="sign-up.php"class="sign-up-btn"data-translate="signUp"style="text-decoration:none"><i class="fa-solid fa-user"style="margin-right:7px"></i> <span class="sign-up-text">Sign Up</span> <span><i class="fas fa-arrow-up-right sign-up-icon"style="color:#fff"></i></span></a></div></div></nav>

<div class="page-search-container"><form action="search.php"class="page-search-form"><div class="page-search-input-wrapper"><div class="input-with-icon"><input class="page-search-input"name="q"placeholder="Enter your search term..."required value="<?php echo htmlspecialchars($searchTerm); ?>"> <span class="main-close-icon"><i class="fas fa-times"></i></span></div><button class="page-search-button"type="submit"><i class="fas fa-search"></i> Search</button></div></form>

    <?php if($searchTerm && !$error): ?>
        <div class="search-results-section">
            <div class="search-results-count">
                <?php echo count($searchResults); ?> results found for "<?php echo htmlspecialchars($searchTerm); ?>"
            </div>
            
            <?php if(empty($searchResults)): ?>
                <div class="no-results-message">
                    <i class="fas fa-search"></i>
                    <p>No results found for "<?php echo htmlspecialchars($searchTerm); ?>"</p>
                    <p>Try different keywords or check your spelling</p>
                </div>
            <?php else: ?>
                <?php foreach($searchResults as $result): ?>
                    <a href="<?php echo htmlspecialchars($result['url']); ?>" class="search-result-card">
                        <h3 class="result-title-link"><?php echo htmlspecialchars($result['title']); ?></h3>
                        <div class="result-excerpt"><?php echo $result['excerpt']; ?></div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><script type="text/javascript">function googleTranslateElementInit(){new google.translate.TranslateElement({pageLanguage:"en"},"google_translate_element")}</script><script>document.addEventListener("DOMContentLoaded", function () {
          function resizeNavLinksByLanguage() {
              const language = document.documentElement.lang;
              const navLinks = document.querySelectorAll('.nav-link');
              let baseFontSize = 16;

              let totalLength = 0;
              navLinks.forEach(link => {
                  totalLength += link.textContent.length;
              });

              let avgLength = totalLength / navLinks.length;

              if (avgLength < 10) {
                  baseFontSize = 15;
              } else if (avgLength >= 10 && avgLength < 20) {
                  baseFontSize = 15;
              } else if (avgLength >= 20) {
                  baseFontSize = 14; 
              }

              navLinks.forEach(link => {
                  link.style.fontSize = `${baseFontSize}px`;
              });
          }

          resizeNavLinksByLanguage();

          const observer = new MutationObserver(resizeNavLinksByLanguage);
          observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });
      });</script><script src="translator.js"type="text/javascript"></script><script>const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          const iframe = document.querySelector('.VIpgJd-ZVi9od-ORHb-OEVmcd');
          if (iframe) {
            iframe.style.visibility = 'hidden';
          }

          const googGtVtElements = document.querySelectorAll('.goog-gt-vt');
          googGtVtElements.forEach(function(element) {
            element.style.visibility = 'hidden';
          });

          if (iframe || googGtVtElements.length > 0) {
            observer.disconnect();
          }

          const elementsToRemove = [
            '.VIpgJd-yAWNEb-hvhgNd-l4eHX-i3jM8c',
            '.VIpgJd-yAWNEb-hvhgNd-k77Iif-i3jM8c',
            '.VIpgJd-yAWNEb-hvhgNd-N7Eqid-B7I4Od',
            '.ltr',
            '.VIpgJd-ZVi9od-aZ2wEe-wOHMyf',
            '.VIpgJd-ZVi9od-aZ2wEe-OiiCO',
            '.VIpgJd-ZVi9od-aZ2wEe',
            '.VIpgJd-ZVi9od-aZ2wEe-Jt5cK'
          ];
          elementsToRemove.forEach(function(className) {
            const elements = document.querySelectorAll(className);
            elements.forEach(function(element) {
              element.remove();
            });
          });
        });
      });

      observer.observe(document.body, { childList: true, subtree: true });

      setInterval(function() {
        document.body.style.top = '0';
      }, 100);

      window.addEventListener('load', function () {
        function getTextWidth(text) {
          const canvas = document.createElement('canvas');
          const context = canvas.getContext('2d');
          context.font = window.getComputedStyle(document.body).font;
          return context.measureText(text).width;
        }

        function initTranslateWidget() {
          const selectElement = document.querySelector('.goog-te-combo');
        }

        const observer = new MutationObserver(function (mutationsList) {
          for (let mutation of mutationsList) {
            if (mutation.type === 'childList' && document.querySelector('.goog-te-combo')) {
              observer.disconnect();
              initTranslateWidget();
            }
          }
        });

        observer.observe(document.body, { childList: true, subtree: true });

        const selectElement = document.querySelector('.goog-te-combo');
      });</script><script>document.addEventListener('DOMContentLoaded', function () {
        const observer = new MutationObserver(function (mutations) {
          mutations.forEach(function (mutation) {
            const select = document.querySelector('.goog-te-combo');
            if (select) {
              observer.disconnect();

              observeSelectOptions(select);

              select.addEventListener('change', function () {
                adjustSelectWidth(select);
              });
            }
          });
        });

        observer.observe(document.body, { childList: true, subtree: true });
      });

      function observeSelectOptions(select) {
        const optionsObserver = new MutationObserver(function () {
          if (select.options.length > 0) {
            adjustSelectWidth(select);

            optionsObserver.disconnect();
          }
        });

        optionsObserver.observe(select, { childList: true });
      }

      function adjustSelectWidth(select) {
        if (select.options.length === 0) {
          console.error('The <select> element has no options available.');
          return;
        }

        const selectedOption = select.options[select.selectedIndex];

        if (!selectedOption) {
          console.error('No option is selected in the <select> element.');
          return;
        }

        const span = document.createElement('span');
        span.style.visibility = 'hidden';
        span.style.position = 'absolute';
        span.style.whiteSpace = 'nowrap';
        span.textContent = selectedOption.textContent;
        document.body.appendChild(span);

        const optionWidth = span.offsetWidth + 15;


        select.style.width = optionWidth + 'px'; 

        document.body.removeChild(span);
      }</script><script>var prevScrollpos=window.pageYOffset;window.onscroll=function(){var o=window.pageYOffset,e=document.getElementById("navbar");e&&(o<prevScrollpos?e.style.top="0":window.matchMedia("(pointer: coarse)").matches?e.style.top="-196px":e.style.top="-100px"),prevScrollpos=o}</script><script>const searchTerm = '<?php echo htmlspecialchars($searchTerm); ?>';
      if (searchTerm) {
          document.title = 'Search Results' + ' | ' + searchTerm;
      }</script><script>document.addEventListener('DOMContentLoaded', function() {
      // Select the close icon and the search input field
      const closeIcon = document.querySelector('.main-close-icon');
      const searchInput = document.querySelector('.page-search-input');

      // Check if both elements are found
      if (closeIcon && searchInput) {
          // Add click event listener to the close icon
          closeIcon.addEventListener('click', function() {
              searchInput.value = ''; // Clear the input field
          });
      }
  });</script>
    <script>
    // Add this script to highlight the text when landing on the target page
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash && window.location.hash.startsWith('#search-')) {
            const position = parseInt(window.location.hash.replace('#search-', ''));
            if (!isNaN(position)) {
                // Create a range around the search position
                const text = document.body.textContent;
                const start = Math.max(0, position - 100);
                const end = Math.min(text.length, position + 100);
                const searchText = text.substring(start, end);
                
                // Find all text nodes and highlight the matching text
                const walk = document.createTreeWalker(
                    document.body,
                    NodeFilter.SHOW_TEXT,
                    null,
                    false
                );
                
                let node;
                while (node = walk.nextNode()) {
                    if (node.textContent.includes(searchText)) {
                        const span = document.createElement('span');
                        span.style.backgroundColor = '#f0d4fc';
                        const range = document.createRange();
                        range.selectNode(node);
                        range.surroundContents(span);
                        
                        // Scroll to the highlighted text
                        span.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        break;
                    }
                }
            }
        }
    });
    </script>
  </body></html>