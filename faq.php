<?php error_reporting(E_ALL);ini_set('display_errors',1);function sanitizeInput($input){return htmlspecialchars(strip_tags(trim($input)));}function highlightText($text,$searchTerm){return preg_replace("/($searchTerm)/i",'<span class="search-highlight">$1</span>',$text);}$faqs=[["question"=>"What is the minimum wage in the UAE?","answer"=>"The minimum wage in the UAE depends on the type of employment and industry, but generally, there isn't a specific federal minimum wage. Salaries are negotiated between the employer and employee."],["question"=>"How can I get a work permit in the UAE?","answer"=>"You can apply for a work permit through your employer once a job offer has been made. The employer will sponsor your work visa, and you must provide necessary documentation such as educational certificates and a medical fitness report."],["question"=>"What is the process for applying for a labor card?","answer"=>"The labor card is typically applied for by your employer through the Ministry of Human Resources and Emiratisation (MOHRE). Once issued, it allows you to work legally in the UAE."],["question"=>"Do I need a visa to enter the UAE?","answer"=>"Yes, most nationalities require a visa to enter the UAE. You can apply for a tourist visa, business visa, or transit visa, depending on the purpose of your stay. Some nationalities can obtain a visa on arrival."],["question"=>"How long does it take to process a work visa?","answer"=>"Processing times for a work visa can range from 1 to 4 weeks, depending on the employer, type of visa, and your nationality."],["question"=>"Can my family join me in the UAE on my work visa?","answer"=>"Yes, once you have a valid work visa, you can sponsor your immediate family (spouse, children, and in some cases, parents) to live in the UAE. You must meet certain salary requirements to be eligible to sponsor your family."],["question"=>"How much does it cost to live in the UAE?","answer"=>"The cost of living in the UAE can vary significantly based on your lifestyle and location. Dubai and Abu Dhabi are more expensive compared to other cities. Rent, utilities, and schooling (if you have children) can be the largest expenses."],["question"=>"Is healthcare free for expats in the UAE?","answer"=>"Healthcare in the UAE is not free for expats. However, it is often provided as part of an employment package. Many employers offer health insurance for employees and their families. There are both public and private healthcare options available."],["question"=>"What is the legal drinking age in the UAE?","answer"=>"The legal drinking age in the UAE is 21. However, alcohol can only be consumed in licensed establishments such as hotels, bars, or private clubs. It is illegal to drink alcohol in public places."],["question"=>"What are the working hours in the UAE?","answer"=>"The standard working hours in the UAE are 48 hours per week, typically 8 hours per day, 6 days a week. During Ramadan, working hours are reduced to 6 hours per day."],["question"=>"What is the public transportation system like in the UAE?","answer"=>"The UAE has a well-developed public transport system, particularly in cities like Dubai and Abu Dhabi. This includes buses, taxis, and the Dubai Metro. Public transport is safe, affordable, and widely used by residents."],["question"=>"What is the culture like in the UAE?","answer"=>"The UAE has a rich and diverse culture, blending traditional Arab customs with modern influences. It is a cosmopolitan country, but it is also important to respect local customs and laws, particularly when it comes to dress code, behavior, and public conduct."],["question"=>"Is it legal to drive in the UAE with an international driving license?","answer"=>"An international driving license is valid for up to 6 months after arrival in the UAE. After this period, expats must obtain a UAE driving license. Depending on your home country, you may be able to convert your foreign license into a UAE one without taking a test."],["question"=>"How do I open a bank account in the UAE?","answer"=>"To open a bank account in the UAE, you will need a valid passport, a residency visa, a salary certificate, and proof of address. Many banks offer various types of accounts, including savings, checking, and salary accounts."],["question"=>"What are the tax rates in the UAE?","answer"=>"The UAE does not have personal income tax. However, there is a Value Added Tax (VAT) of 5% on most goods and services. Some sectors, like oil and gas, may be subject to additional taxes."],["question"=>"What are the rules regarding dress code in the UAE?","answer"=>"The UAE has a modest dress code, particularly in public places. While tourists and expats can wear casual clothes, it is important to dress conservatively, especially in public and religious places. Women should avoid wearing revealing clothes, and men should avoid wearing sleeveless shirts in public spaces."],["question"=>"Can I practice my religion freely in the UAE?","answer"=>"The UAE is a Muslim country, and Islam is the state religion. However, the country allows freedom of worship for other religions, and there are places of worship for Christians, Hindus, Sikhs, and others. Public proselytizing and the display of religious symbols may be restricted."],["question"=>"What is the weather like in the UAE?","answer"=>"The UAE has a desert climate, characterized by hot summers (temperatures can exceed 40°C/104°F) and mild winters (around 20-25°C/68-77°F). Rain is rare, and humidity can be high, particularly along the coast."],["question"=>"What is the etiquette for tipping in the UAE?","answer"=>"Tipping is appreciated but not mandatory in the UAE. A 10% tip in restaurants is common if service charges are not included in the bill. Hotel staff, taxi drivers, and service workers may also expect small tips."],["question"=>"What is the school system like in the UAE?","answer"=>"The UAE has both public and private schools, with private schools following various curricula (British, American, International Baccalaureate, etc.). Public schools are primarily for Emirati students, while expatriate children typically attend private schools. The school year runs from September to June."],["question"=>"Can I bring my pet to the UAE?","answer"=>"Yes, pets are allowed in the UAE, but there are strict import regulations, including vaccinations, microchipping, and a health certificate from a licensed veterinarian. You will need to go through customs and animal quarantine procedures upon arrival."],["question"=>"What is the emergency number in the UAE?","answer"=>"The emergency number in the UAE is 999 for police, fire, and ambulance services."],["question"=>"Are there any restrictions on bringing personal items into the UAE?","answer"=>"While personal items such as clothing and electronics can be brought into the UAE duty-free, there are restrictions on certain items such as drugs, pornography, and gambling devices. It's important to check the UAE Customs regulations before traveling."],["question"=>"Can I use my mobile phone in the UAE?","answer"=>"Yes, you can use your mobile phone in the UAE. There are two major mobile service providers: Etisalat and du. Prepaid and postpaid plans are available, and you can also purchase SIM cards at the airport or in retail stores."],["question"=>"Is it safe to live in the UAE?","answer"=>"The UAE is considered one of the safest countries in the Middle East, with low crime rates and strict laws. However, it is important to be aware of local laws and customs, as breaking them can result in fines, deportation, or even jail time."],["question"=>"What is the legal system in the UAE?","answer"=>"The UAE's legal system is based on both civil law and Sharia law. While the legal framework supports modern commercial law, Sharia law governs personal matters such as marriage, divorce, and inheritance, especially for Muslims."],["question"=>"How can I renew my visa in the UAE?","answer"=>"Visa renewal is typically handled by your employer or sponsor. You may need to undergo a medical check-up, and your residency permit will need to be updated based on your employment status. Be sure to renew your visa before it expires to avoid penalties."],["question"=>"What are the weekend days in the UAE?","answer"=>"The UAE's official weekend is Saturday and Sunday. Friday is considered a holy day for Muslims, so many businesses close in the morning for prayers and open later in the afternoon."],];$searchResults=[];$searchTerm='';$error='';if($_SERVER['REQUEST_METHOD']==='GET'&&isset($_GET['q'])){$searchTerm=sanitizeInput($_GET['q']);if(strlen($searchTerm)<2){$error='Please enter at least 2 characters';}else{foreach($faqs as $faq){$questionHighlighted=highlightText($faq['question'],$searchTerm);$answerHighlighted=highlightText($faq['answer'],$searchTerm);if(stripos($faq['question'],$searchTerm)!==false||stripos($faq['answer'],$searchTerm)!==false){$searchResults[]=['question'=>$questionHighlighted,'answer'=>$answerHighlighted];}}}} ?><!doctypehtml><html lang="en"><head><meta charset="UTF-8"><meta content="width=device-width,initial-scale=1"name="viewport"><title>FAQ - Search</title><link href="/favicon/favicon.ico"rel="icon"type="image/x-icon"><link href="/favicon/favicon-16x16.png"rel="icon"sizes="16x16"type="image/png"><link href="/favicon/favicon-32x32.png"rel="icon"sizes="32x32"type="image/png"><link href="/favicon/apple-touch-icon.png"rel="apple-touch-icon"sizes="180x180"><link href="/favicon/android-chrome-192x192.png"rel="icon"sizes="192x192"><link href="/favicon/android-chrome-512x512.png"rel="icon"sizes="512x512"><link href="/favicon/site.webmanifest"rel="manifest"><link href="all.css"rel="stylesheet"><link href="Lato.css"rel="stylesheet"><link href="main.css"rel="stylesheet"><style>.page-search-container{max-width:800px;margin:0 auto;padding:1rem}.page-search-form{margin-top:100px;background:#fff;padding:2rem;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1)}.page-search-input-wrapper{display:flex;gap:1rem;position:relative}.page-search-input{flex-grow:1;padding:1rem 1.5rem;font-size:1.1rem;border:2px solid #e1e1e1;border-radius:6px;transition:border-color .2s ease}.page-search-input:focus{border-color:#0056b3;outline:0}.page-search-button{padding:1rem 2rem;background-color:#0056b3;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;transition:background-color .2s ease;display:flex;align-items:center;gap:.5rem}.page-search-button:hover{background-color:#004494}.search-results-section{margin-top:2rem;padding:1rem;background-color:#f9f9f9;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1)}.search-results-count{margin-bottom:1.5rem;color:#555;font-size:1.1rem;font-weight:700;padding-bottom:1rem;border-bottom:2px solid #eee}.search-result-card{margin-bottom:2rem;padding:1.5rem;border:1px solid #e1e1e1;border-radius:8px;background-color:#fff;box-shadow:0 2px 8px rgba(0,0,0,.05);transition:all .3s ease-in-out;font-family:Lato,sans-serif}.search-result-card:hover{transform:translateY(-5px);box-shadow:0 4px 12px rgba(0,0,0,.15)}.result-title{color:#0056b3;font-size:1.4rem;font-weight:700;margin-bottom:1rem;transition:color .2s ease}.result-title:hover{color:#004494;text-decoration:underline}.result-url{color:#006621;font-size:.9rem;margin-bottom:.75rem;word-break:break-all}.result-excerpt{color:#545454;font-size:1rem;line-height:1.7}.search-highlight{background-color:#fff3cd;padding:.2rem .0rem;border-radius:3px;font-weight:500}.search-error{color:#dc3545;padding:1.5rem;background-color:#f8d7da;border-radius:8px;margin-bottom:1.5rem;text-align:center;font-size:1rem;font-weight:700}.no-results-message{text-align:center;padding:3rem 2rem;color:#666;background-color:#f8f9fa;border-radius:8px;margin-top:2rem;font-size:1.2rem}.no-results-message i{font-size:3rem;color:#999;margin-bottom:1.5rem;display:block}.result-answer{color:#333;font-size:1.1rem;line-height:1.6;margin-bottom:1rem}</style></head><body><nav class="navbar"><div class="container"><a href="home.html"><img alt="Logo"class="logo"src="logo.png"></a><ul class="nav-links"><li class="nav-item"><a href="home.html"class="nav-link"data-translate="home">Home</a></li><li class="nav-item"><a href="getting-started.html"class="nav-link"data-translate="gettingStarted"aria-expanded="false"aria-haspopup="true">Getting Started <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="important-documents.html"class="dropdown-link"data-translate="importantDocuments">Important Documents</a> <a href="housing.html"class="dropdown-link"data-translate="housing">Housing</a> <a href="healthcare.html"class="dropdown-link"data-translate="healthcare">Healthcare</a> <a href="cost-of-living.html"class="dropdown-link"data-translate="costOfLiving">Cost of Living</a></div></li><li class="nav-item"><a href="living-in-the-uae.html"class="nav-link"data-translate="livingInUAE"aria-expanded="false"aria-haspopup="true">Living in the UAE <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="culture-and-customs.html"class="dropdown-link"data-translate="cultureAndCustoms">Culture and Customs</a> <a href="transportation.html"class="dropdown-link"data-translate="transportation">Transportation</a> <a href="shopping.html"class="dropdown-link"data-translate="shopping">Shopping</a></div></li><li class="nav-item"><a href="education.html"class="nav-link"data-translate="education"aria-expanded="false"aria-haspopup="true">Education <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="curriculum-guides.html"class="dropdown-link"data-translate="curriculumGuides">Curriculum Guides</a> <a href="school-listings.html"class="dropdown-link"data-translate="schoolListings">School Listings</a></div></li><li class="nav-item"><a href="working-in-the-uae.html"class="nav-link"data-translate="workingInUAE"aria-expanded="false"aria-haspopup="true">Working in the UAE <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="labour-card.html"class="dropdown-link"data-translate="labourCard">Labour Card</a> <a href="job-market-insights.html"class="dropdown-link"data-translate="jobMarketInsights">Job Market Insights</a> <a href="labour-laws.html"class="dropdown-link"data-translate="labourLaws">Labour Laws</a> <a href="becoming-an-entrepreneur.html"class="dropdown-link"data-translate="becomingEntrepreneur">Becoming an Entrepreneur</a> <a href="networking-oppurtunities.html"class="dropdown-link"data-translate="networkingOpportunities">Networking Opportunities</a></div></li><li class="nav-item"><a href="community-and-support.html"class="nav-link"data-translate="communitySupport"aria-expanded="false"aria-haspopup="true">Community & Support <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="faq.php"class="dropdown-link"data-translate="faq">FAQ</a> <a href="contact-us.php"class="dropdown-link"data-translate="contactUs">Contact Us</a></div></li></ul><div class="nav-right"><div class="search-container"><input class="search-input"name="q"placeholder="Search..."><div class="search-icons-container"><i class="fas fa-times close-icon"id="close-icon"></i> <button class="search-btn"type="button"id="search-btn"><i class="fas fa-search"></i></button></div></div><div class="lang-dropdown"><button class="lang-btn"><i class="fa-globe fal"style="color:#fff"></i><div id="google_translate_element"></div></button></div><a href="sign-up.php"class="sign-up-btn"data-translate="signUp"style="text-decoration:none"><i class="fa-solid fa-user"style="margin-right:7px"></i> <span class="sign-up-text">Sign Up</span> <span><i class="fas fa-arrow-up-right sign-up-icon"style="color:#fff"></i></span></a></div></div></nav><div class="page-search-container"><form action="faq.php"class="page-search-form"><div class="page-search-input-wrapper"><div class="input-with-icon"><input class="page-search-input"name="q"placeholder="Search FAQ..."id="search-input"required value="<?php echo htmlspecialchars($searchTerm); ?>"> <span class="main-close-icon"><i class="fas fa-times"></i></span></div><button class="page-search-button"type="submit"><i class="fas fa-search"></i> Search</button></div></form><?php if($error): ?><div class="search-error"><?php echo $error; ?></div><?php endif; ?><?php if($searchTerm&&!$error): ?><div class="search-results-section"><div class="search-results-count"><?php echo count($searchResults); ?> result(s) found for "<?php echo htmlspecialchars($searchTerm); ?>"</div><?php if(empty($searchResults)): ?><div class="no-results-message"><i class="fas fa-search"></i><p>No results found for "<?php echo htmlspecialchars($searchTerm); ?>"</p><p>Try different keywords or check your spelling</p><br><p>If you have any FAQ question suggestions <button onclick='window.location.href="contact-us"'style="background-color:#3498db;color:#fff;padding:10px;border-color:#fff">Go to Contact Us</button></p></div><?php else: ?><?php foreach($searchResults as $result): ?><div class="search-result-card"><p style="font-weight:900">Question: <?php echo $result['question']; ?></p><br><p style="font-weight:900">Answer: <?php echo $result['answer']; ?></p></div><?php endforeach; ?><?php endif; ?></div><?php endif; ?></div><script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><script type="text/javascript">function googleTranslateElementInit(){new google.translate.TranslateElement({pageLanguage:"en"},"google_translate_element")}</script><script>document.addEventListener("DOMContentLoaded", function () {
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
        console.log('Calculated width:', optionWidth); 

        select.style.width = optionWidth + 'px'; 

        document.body.removeChild(span);
      }</script><script>var prevScrollpos=window.pageYOffset;window.onscroll=function(){var o=window.pageYOffset,e=document.getElementById("navbar");e&&(o<prevScrollpos?e.style.top="0":window.matchMedia("(pointer: coarse)").matches?e.style.top="-196px":e.style.top="-100px"),prevScrollpos=o}</script><script>document.addEventListener('DOMContentLoaded', function() {
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
  });</script></body></html>