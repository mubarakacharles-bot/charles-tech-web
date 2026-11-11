(function () {
  'use strict';

  var languagePairs = [
    ['Home', 'Ahabanza'], ['Shop', 'Isoko'], ['Learn', 'Kwiga'], ['Hot Deals', 'Ibiciro byagabanyijwe'],
    ['Team', 'Itsinda'], ['About', 'Ibyacu'], ['Contact', 'Twandikire'], ['Tutorials', 'Inyigisho'],
    ['Education', 'Amasomo'], ['Project Adviser', 'Umujyanama w’imishinga'], ['Project Adviser (in Learn)', 'Umujyanama w’imishinga (mu Amasomo)'],
    ['Documentations', 'Inyandiko'], ['Our Team', 'Itsinda ryacu'], ['Track your Order', 'Kurikirana ibyo watumije'],
    ['Student Portal · Attendance & Stock', 'Urubuga rw’Abanyeshuri · Ubwitabire n’Ububiko'],
    ['Account', 'Konti'], ['Login', 'Injira'], ['Register (Sign Up)', 'Iyandikishe'], ['Register', 'Iyandikishe'],
    ['User Profile', 'Umwirondoro'], ['Checkout', 'Kwishyura'], ['Logout', 'Sohoka'], ['Admin Dashboard', 'Imbonerahamwe y’Ubuyobozi'],
    ['⌂ Home', '⌂ Ahabanza'], ['🛍 Shop', '🛍 Isoko'], ['🔥 Hot Deals', '🔥 Ibiciro byagabanyijwe'], ['🎓 Education', '🎓 Amasomo'],
    ['🧭 Project Adviser (in Learn)', '🧭 Umujyanama w’imishinga (mu Amasomo)'], ['📄 Documentations', '📄 Inyandiko'], ['🎬 Tutorials', '🎬 Inyigisho'],
    ['👥 Our Team', '👥 Itsinda ryacu'], ['🏢 About', '🏢 Ibyacu'], ['✉ Contact', '✉ Twandikire'],
    ['📦 Track your Order', '📦 Kurikirana ibyo watumije'], ['🎓 Student Portal · Attendance & Stock', '🎓 Urubuga rw’Abanyeshuri · Ubwitabire n’Ububiko'],
    ['🔑 Login', '🔑 Injira'], ['📝 Register (Sign Up)', '📝 Iyandikishe'], ['👤 User Profile', '👤 Umwirondoro'],
    ['💳 Checkout', '💳 Kwishyura'], ['🚪 Logout', '🚪 Sohoka'], ['⚙️ Admin Dashboard', '⚙️ Imbonerahamwe y’Ubuyobozi'],
    ['Open navigation menu', 'Fungura menu y’urubuga'], ['Close menu', 'Funga menu'], ['Site navigation', 'Uduce tw’urubuga'],
    ['Search products and projects', 'Shakisha ibicuruzwa n’imishinga'], ['Category', 'Icyiciro'], ['All', 'Byose'], ['Search', 'Shakisha'],
    ['Search phones, laptops, Arduino, cables…', 'Shakisha telefoni, mudasobwa, Arduino, insinga…'], ['Chat with us', 'Twandikire'],
    ['Chat with us on WhatsApp', 'Twandikire kuri WhatsApp'], ['WELCOME BACK', 'URAKAZE NEZA'], ['JOIN FREE', 'IYANDIKISHE KU BUNTU'],
    ['Main site navigation', 'Menu nyamukuru y’urubuga'], ['Customer information', 'Amakuru agenewe abakiriya'],
    ['AMASOMO NGIRO · MUSANZE, RWANDA', 'PRACTICAL TRAINING · MUSANZE, RWANDA'],
    ['Ubutumwa bwa Charles Tech', 'Charles Tech messages'], ['Kwiga no kubaka imishinga ya Embedded Systems na IoT i Musanze', 'Learn and build Embedded Systems and IoT projects in Musanze'],
    ['Microcontroller projects for Embedded Systems learners', 'Imishinga ya microcontroller y’abiga Embedded Systems'], ['Charles Tech practical technology workshop in Musanze', 'Amahugurwa ngiro y’ikoranabuhanga ya Charles Tech i Musanze'],
    ['ESP32 and circuit-board design for connected projects', 'ESP32 n’igishushanyo cya circuit board by’imishinga ihujwe'], ['Microcontroller board connected to sensors and electronic components', 'Board ya microcontroller ihujwe na sensors n’ibikoresho bya elegitoroniki'],
    ['Electronic components used to build embedded circuits', 'Ibikoresho bya elegitoroniki byo kubaka circuits za embedded'], ['Printed circuit board designed for an embedded project', 'Circuit board yagenewe umushinga wa embedded'],
    ['Collection of sensors and wireless modules used in IoT projects', 'Sensors na modules zitumanaho zikoreshwa mu mishinga ya IoT'], ['Sensor modules for collecting real-world data', 'Sensor modules zikusanya amakuru nyakuri'],
    ['Documentation banner', 'Ifoto y’inyandiko'], ['Tutorials banner', 'Ifoto y’inyigisho'], ['About Charles Tech banner', 'Ifoto ivuga kuri Charles Tech'], ['Contact Charles Tech banner', 'Ifoto yo kutwandikira Charles Tech'],
    ['Murakaza neza', 'Welcome'], ['muri Innovation Hub', 'to the Innovation Hub'],
    ['Menya ibikoresho by’ikoranabuhanga, wige Embedded Systems na IoT, uhindure ibitekerezo byawe imishinga ifatika.', 'Explore technology, learn Embedded Systems and IoT, and turn your ideas into real projects.'],
    ['CODE IHURA N’IBIKORESHO', 'CODE MEETS HARDWARE'], ['Kora code.', 'Write code.'], ['Huza ibikoresho.', 'Connect devices.'],
    ['Korana na Arduino, ESP32 na sensors wige Embedded Systems na IoT ukoresheje imyitozo ngiro.', 'Work with Arduino, ESP32 and sensors as you learn Embedded Systems and IoT through hands-on practice.'],
    ['UMUSHINGA UTANGIRA HANO', 'YOUR PROJECT STARTS HERE'], ['Ibitekerezo byawe', 'Your ideas'], ['bikwiye kubakwa.', 'are worth building.'],
    ['Iga intambwe ku yindi, gerageza ibyo wize, kandi wungurane ibitekerezo n’abatoza.', 'Learn step by step, put your skills to work, and get feedback from our trainers.'],
    ['Reba amasomo', 'Explore courses'], ['Vugana natwe kuri WhatsApp', 'Talk to us on WhatsApp'], ['IGA', 'LEARN'], ['UKORE', 'BUILD'], ['UHUZE', 'CONNECT'],
    ['WIGE UBUKOREZI. WUBAKE KIZAZA.', 'LEARN BY BUILDING. CREATE WHAT’S NEXT.'], ['Ubumenyi uhita ukoresha.', 'Skills you can put to work.'],
    ['Tangirira ku musingi, ukore imyitozo ku bikoresho nyakuri, urangize ufite umushinga ushobora kwereka abandi.', 'Start with the fundamentals, practise on real equipment, and finish with a project you can show.'],
    ['Arduino · STM32 · Sensors', 'Arduino · STM32 · Sensors'], ['Embedded Systems', 'Embedded Systems'],
    ['Andika code igenzura ibikoresho nyakuri. Tangira kuri Arduino na sensors, ukomeze kuri STM32, GPIO na C/C++ uko utera imbere.', 'Write code that controls real devices. Start with Arduino and sensors, then progress to STM32, GPIO and C/C++.'],
    ['ESP32 · Wi-Fi · MQTT', 'ESP32 · Wi-Fi · MQTT'], ['Internet of Things (IoT)', 'Internet of Things (IoT)'],
    ['Huza sensors n’interineti ukoresheje ESP32, Wi-Fi na MQTT. Ohereza, reba kandi ukoreshe amakuru yavuye ku gikoresho cyawe.', 'Connect sensors to the internet with ESP32, Wi-Fi and MQTT. Send, view and use data from your own device.'],
    ['Iga. Gerageza. Wubake ikintu cyawe.', 'Learn. Experiment. Build something of your own.'],
    ['Ntugarukire ku kureba amasomo. Korera ku bikoresho, gerageza ibitekerezo byawe, kandi wungurane ibitekerezo n’abatoza igihe cyose utegura umushinga wawe.', 'Go beyond watching lessons. Work with real equipment, test your ideas, and get trainer guidance as your project takes shape.'],
    ['Reba amasomo yose', 'Explore all courses'], ['WIGE UBUKOREZI. WUBAKE KIZAZA.', 'LEARN BY BUILDING. CREATE WHAT’S NEXT.'],
    ['Hitamo aho utangirira', 'Choose where to begin'], ['Amasomo agufasha gusobanukirwa ikoranabuhanga no kurikoresha mu mishinga ifatika.', 'Learn how technology works and apply it to practical projects.'],
    ['Menya uko microcontroller igenzura ibikoresho. Korana na Arduino na STM32, sensors, GPIO na C/C++.', 'Learn how microcontrollers control devices. Work with Arduino and STM32, sensors, GPIO and C/C++.'],
    ['Huza ibikoresho na interineti ukoresheje ESP32/ESP8266, Wi-Fi na MQTT; ukurikirane amakuru aturutse kuri sensors.', 'Connect devices to the internet with ESP32/ESP8266, Wi-Fi and MQTT, and monitor data from sensors.'],
    ['Iga. Gerageza. Wubake ikintu cyawe.', 'Learn. Experiment. Build something of your own.'],
    ['Explore learning, project directory and more', 'Menya amasomo, urutonde rw’imishinga n’ibindi'],
    ['🏭 Charles Tech · Musanze, Rwanda', '🏭 Charles Tech · Musanze, Rwanda'], ['Learn IT where real engineers work', 'Iga ikoranabuhanga ukoreshwa mu kazi nyakuri'],
    ['Charles High Technology Solution Ltd designs embedded systems and IoT — and trains IT learners with hands-on practice, not just theory.', 'Charles High Technology Solution Ltd ikora Embedded Systems na IoT, ikanigisha abanyeshuri ikoranabuhanga binyuze mu myitozo ngiro.'],
    ['💻 CSA · Computer System & Architecture', '💻 CSA · Imiterere n’imikorere ya Mudasobwa'], ['See how a computer really works inside', 'Menya uko mudasobwa ikora imbere'],
    ['Processors, memory, buses and boards — practise CSA on real hardware with tasks, feedback and a discussion board.', 'Menya processors, memory, buses na boards, wimenyereze kuri hardware nyakuri kandi uhabwe ibitekerezo ku byo wakoze.'],
    ['🌐 NIT · Networking & Internet Technology', '🌐 NIT · Imiyoboro n’Ikoranabuhanga rya Interineti'], ['Build networks, don’t just read about them', 'Shyiraho imiyoboro aho kuyisoma gusa'],
    ['Cabling, IP addressing, routers, switches and IoT connectivity — learn NIT by connecting and testing real devices.', 'Iga insinga, aderesi za IP, routers, switches n’imiyoboro ya IoT uhuza kandi ugerageza ibikoresho nyakuri.'],
    ['🧠 Software · AI · more IT courses', '🧠 Porogaramu · AI · andi masomo ya IT'], ['From your first line of code to edge AI', 'Kuva kuri code ya mbere kugeza kuri Edge AI'],
    ['Programming, Raspberry Pi and AI projects for every IT course — submit your work with photos and code.', 'Kora porogaramu n’imishinga ya Raspberry Pi na AI, wohereze ibyo wakoze hamwe n’amafoto na code.'],
    ['🎓 Student Portal', '🎓 Urubuga rw’Abanyeshuri'], ['Register, practise, get feedback', 'Iyandikishe, wimenyereze, uhabwe ibitekerezo'],
    ['Create your own student account, see the practice tasks for your class, submit your work and join the discussion.', 'Fungura konti y’umunyeshuri, reba imyitozo y’ishuri ryawe, ohereza ibyo wakoze kandi ugire uruhare mu biganiro.'],
    ['🛒 Shop Now', '🛒 Gura nonaha'], ['🎓 Start Learning', '🎓 Tangira kwiga'], ['🎓 OUR PURPOSE', '🎓 INTEGO YACU'],
    ['Helping IT learners in Rwanda turn theory into real skill', 'Gufasha abiga IT mu Rwanda guhindura ubumenyi mu bikorwa'],
    ['Charles High Technology Solution Ltd is a Rwandan technology company building embedded systems, IoT and 3D-design projects.', 'Charles High Technology Solution Ltd ni ikigo nyarwanda cy’ikoranabuhanga gikora imishinga ya Embedded Systems, IoT na 3D Design.'],
    ['This website exists to educate and improve learners who study IT courses — such as CSA and NIT — with real hardware, practice tasks and trainer feedback.', 'Uru rubuga rugamije kwigisha no guteza imbere abiga amasomo ya IT nka CSA na NIT, binyuze kuri hardware nyakuri, imyitozo n’ibitekerezo by’abatoza.'],
    ['📍 Visit us Musanze District, Rwanda', '📍 Dusange mu Karere ka Musanze, Rwanda'], ['🕗 Opening hours 8:00 – 19:30 · closed Sunday', '🕗 Amasaha y’akazi 8:00 – 19:30 · Ku cyumweru turafunze'],
    ['🛠️ What we do Embedded systems, IoT, Raspberry Pi & 3D design', '🛠️ Ibyo dukora: Embedded Systems, IoT, Raspberry Pi na 3D Design'], ['📞 Call / WhatsApp +250 787 767 610', '📞 Hamagara cyangwa wandike kuri WhatsApp +250 787 767 610'],
    ['IT courses we help you master', 'Amasomo ya IT tugufasha kumenya neza'], ['Start learning →', 'Tangira kwiga →'], ['🎓 Join the Student Portal', '🎓 Injira mu Rubuga rw’Abanyeshuri'], ['📚 Explore Learn', '📚 Reba amasomo'],
    ['Embedded Systems & IoT Project Directory', 'Urutonde rw’Imishinga ya Embedded Systems na IoT'],
    ['🏭 OUR WORKSHOP · MUSANZE', '🏭 AHO DUKORERA · MUSANZE'], ['Real engineers, real tools, real workspace — not just a catalogue.', 'Abahanga, ibikoresho n’ahakorerwa nyakuri — si urutonde rw’ibicuruzwa gusa.'],
    ['Every project in this directory is designed and tested on this bench: SolidWorks 3D modelling, PrusaSlicer print prep, embedded systems, sensor networks, and Arduino/ESP32 builds.', 'Imishinga iri hano irategurwa kandi ikageragerezwa mu kazi kacu: SolidWorks 3D, PrusaSlicer, Embedded Systems, imiyoboro ya sensors, Arduino na ESP32.'],
    ['🏢 Meet the Team →', '🏢 Menya itsinda ryacu →'], ['The full catalogue — every board, sensor, motor and module. Browse by category and buy at normal prices.', 'Reba boards, sensors, motors na modules zose mu byiciro, ugure ku biciro bisanzwe.'],
    ['Open Shop →', 'Fungura isoko →'], ['Limited-time reduced prices on selected items. A countdown shows when each offer ends or stock runs out.', 'Ibiciro byagabanyijwe ku bicuruzwa byatoranyijwe mu gihe gito. Reba igihe igabanyirizwa rirangirira cyangwa ibicuruzwa bigashira.'], ['See Deals →', 'Reba ibiciro byagabanyijwe →'],
    ['Ibicuruzwa', 'Products'], ['Ibikoresho by’ikoranabuhanga, biboneka i Musanze.', 'Technology supplies available in Musanze.'], ['≡ Categories', '≡ Ibyiciro'],
    ['Reba ibicuruzwa ku giciro cyiza.', 'Find products at great prices.'], ['Reba ibiciro byagabanyijwe →', 'See discounted prices →'],
    ['🔥 IGIHE GITO · IBICIRO BYIZA', '🔥 LIMITED TIME · GREAT PRICES'], ['Igabanyirizwa ridasanzwe', 'Special discounts'], ['Hitamo ibikoresho byagabanyirijwe ibiciro, igihe bigihari.', 'Choose discounted supplies while they are available.'],
    ['⚡ UZIGAME BYINSHI', '⚡ SAVE MORE'], ['Ibikoresho by’umushinga wawe, ku giciro cyiza', 'Project supplies at a great price'], ['Shaka ibikoresho by’ingirakamaro mu mishinga yawe yo guhanga no kwiga.', 'Find useful supplies for your creative and learning projects.'],
    ['🎁 AMAPAKI YIHARIYE', '🎁 SPECIAL BUNDLES'], ['Gura byinshi, wizigamire byinshi', 'Buy more and save more'], ['Hitamo amapaki y’ibikoresho byatoranyijwe ku giciro cyiza.', 'Choose bundles of selected supplies at a great price.'],
    ['🔥 IGABANYIRIZWA RY’IBICIRO · IGIHE GITO', '🔥 PRICE DROP · LIMITED TIME'], ['Ibiciro byagabanyijwe', 'Discounted prices'], ['Igihe gisigaye', 'Time remaining'], ['Ibicuruzwa byose bigabanyirizwa', 'All discounted products'],
    ['Search across every project in the directory AND every product in the shop, in one place.', 'Shakisha imishinga yose n’ibicuruzwa byose byo mu iduka ahantu hamwe.'], ['Type to search projects and products...', 'Andika ushakisha imishinga n’ibicuruzwa...'],
    ['Welcome to Our Team', 'Murakaza neza mu itsinda ryacu'], ['The people behind Charles Tech — Embedded Systems, IoT & 3D Design.', 'Menya abantu bari inyuma ya Charles Tech — Embedded Systems, IoT na 3D Design.'],
    ['🎓 Learn by doing', '🎓 Iga ukoresheje imyitozo'], ['Embedded systems, from the inside', 'Menya imikorere ya Embedded Systems'], ['Microcontrollers, sensors and power — practised on real hardware.', 'Wimenyereze microcontrollers, sensors n’amashanyarazi kuri hardware nyakuri.'],
    ['🧠 AI · IoT', '🧠 AI · IoT'], ['Build smart devices that see and talk', 'Kora ibikoresho by’ubwenge bibona kandi bivugana'], ['IoT and edge-AI projects with tasks and trainer feedback.', 'Imishinga ya IoT na Edge AI irimo imyitozo n’ibitekerezo by’abatoza.'],
    ['🧵 3D design & printing', '🧵 Gushushanya no gucapa muri 3D'], ['From idea to a part in your hand', 'Kura igitekerezo ugere ku gicuruzwa'], ['SolidWorks, slicing and 3D printing for real prototypes.', 'Koresha SolidWorks, slicing na 3D printing ukore prototypes nyakuri.'],
    ['Structured learning tracks that follow our own product categories — so every lesson lines up with parts you can actually buy from the Shop.', 'Amasomo akurikirana ibyiciro by’ibicuruzwa byacu, bityo ibyo wiga bigahuzwa n’ibikoresho ushobora kugura mu iduka.'],
    ['🖼️ Photo gallery', '🖼️ Amafoto'], ['📚 Learning tracks', '📚 Inzira zo kwiga'], ['🧭 Project Adviser', '🧭 Umujyanama w’imishinga'], ['Learning tracks', 'Inzira zo kwiga'],
    ["Self-paced lesson content, quizzes, and certificates are being finalised for each track above. Want a head start today? Chat with us on WhatsApp and we'll send a reading/parts list for the track you're interested in.", 'Amasomo, ibizamini n’impamyabumenyi birategurwa. Kugira ngo utangire uyu munsi, twandikire kuri WhatsApp twohereze inyandiko n’ibikoresho uzakenera.'],
    ['💬 Get a Learning Roadmap on WhatsApp', '💬 Saba gahunda y’amasomo kuri WhatsApp'],
    ["Reference material organised by the same categories as the Shop, so it's easy to find what matches a part you've bought.", 'Inyandiko zitondetse nk’ibyiciro byo mu iduka kugira ngo ubone vuba amakuru ajyanye n’igikoresho waguze.'],
    ['📄 Documentations', '📄 Inyandiko'], ['Development Board Datasheets', 'Amakuru ya Development Boards'], ['Sensor Wiring Diagrams', 'Imbonerahamwe z’Uko Sensors Zihuza'],
    ['Electronic Module Reference Sheets', 'Inyandiko z’Amakuru ya Electronic Modules'], ['AI Module Setup Guides', 'Amabwiriza yo Gushyiraho AI Modules'], ['3D Print & CNC Manuals', 'Amabwiriza ya 3D Printing na CNC'],
    ['Step-by-step, written walkthroughs for popular builds — grouped by category so you can jump straight to what you’re building.', 'Amabwiriza yanditse agufasha gukora imishinga ikunzwe intambwe ku yindi, atondetse mu byiciro.'],
    ['🎬 Tutorials', '🎬 Inyigisho'], ['Ultrasonic Obstacle-Avoiding Robot', 'Robot Yirinda Inzitizi ikoresha Ultrasonic'], ['Wi-Fi Weather Station', 'Sitasiyo y’Iteganyagihe ikoresha Wi-Fi'],
    ['RFID Smart Door Lock', 'Urugi rw’Ubwenge rukoresha RFID'], ['Face-Detection Security Camera', 'Kamera y’Umutekano Imenya Isura'], ['Automated Soil-Moisture Irrigation', 'Kuhira hakurikijwe Ubuhehere bw’Ubutaka'], ['Printing Your First Enclosure', 'Gucapa Agakoresho ka Mbere'],
    ['🧺 Shopping Cart', '🧺 Ikarita y’Ibyo wahisemo'], ['Review the items you’ve added before checking out.', 'Subiramo ibyo wahisemo mbere yo gukomeza ku kwishyura.'], ['Total', 'Igiteranyo'], ['← Continue Shopping', '← Komeza guhaha'], ['Proceed to Checkout →', 'Komeza ku kwishyura →'],
    ['Sign in to track orders and manage your profile.', 'Injira kugira ngo ukurikirane ibyo watumije kandi ucunge umwirondoro wawe.'], ['Email or username', 'Imeyili cyangwa izina ukoresha'], ['Password', 'Ijambo ry’ibanga'],
    ['Remember email on this device', 'Bika imeyili kuri iki gikoresho'], ['Forgot password?', 'Wibagiwe ijambo ry’ibanga?'], ['Login', 'Injira'], ['No account yet? Register here', 'Nta konti uragira? Iyandikishe hano'],
    ['Create a free account to check out faster and view your order history.', 'Fungura konti ku buntu kugira ngo wishyure vuba kandi urebe amateka y’ibyo watumije.'], ['Full Name', 'Amazina yose'], ['Phone (Rwanda)', 'Telefoni (Rwanda)'], ['Email', 'Imeyili'],
    ['Use at least 8 characters.', 'Koresha inyuguti nibura 8.'], ['Create Account', 'Fungura konti'], ['Already registered? Login here', 'Warangije kwiyandikisha? Injira hano'], ['📦 Order History', '📦 Amateka y’Ibyo Watumije'],
    ['All orders placed from this browser.', 'Ibintu byose byatumijwe ukoresheje iyi browser.'], ['Confirm your delivery details and payment method.', 'Emeza amakuru yo kukugezaho ibyo waguze n’uburyo bwo kwishyura.'],
    ['Phone Number', 'Nimero ya Telefoni'], ['Delivery Address / District', 'Aderesi yo Kugeza Ibicuruzwa / Akarere'], ['Payment Method', 'Uburyo bwo Kwishyura'], ['MTN Mobile Money', 'MTN Mobile Money'], ['Airtel Money', 'Airtel Money'],
    ['Cash on Delivery', 'Kwishyura ugezezwaho ibicuruzwa'], ['Bank Transfer', 'Kohereza amafaranga kuri konti ya banki'], ['Transaction Reference / Confirmation Code', 'Nimero y’Igikorwa / Kode y’Iyemeza'], ['Order Total', 'Igiteranyo cy’Ibyatumijwe'], ['Place Order', 'Ohereza ibyo watumije'],
    ['🏢 About Charles Tech', '🏢 Ibyerekeye Charles Tech'], ['Contact Us', 'Twandikire'], ['We usually reply fastest on WhatsApp during working hours. Reach us however is easiest for you:', 'Akenshi dusubiza vuba kuri WhatsApp mu masaha y’akazi. Twandikire uko bikoroheye:'],
    ['Address', 'Aderesi'], ['Musanze District, Rwanda', 'Akarere ka Musanze, Rwanda'], ['Phone', 'Telefoni'], ['WhatsApp', 'WhatsApp'], ['Working Hours', 'Amasaha y’akazi'], ['Mon – Sat, 8:00 – 19:30', 'Kuwa Mbere – Kuwa Gatandatu, 8:00 – 19:30'], ['Closed Sunday', 'Ku Cyumweru turafunze'], ['Send us a message', 'Twandikire ubutumwa'],
    ['This form opens a pre-filled WhatsApp message — we don’t have a mail server connected yet, so nothing here is stored on our website.', 'Iyi fomu ifungura ubutumwa bwa WhatsApp bwateguwe. Ubu ntiturahuza seriveri ya imeyili, bityo amakuru hano ntazigamwa ku rubuga.'],
    ['Phone or email', 'Telefoni cyangwa imeyili'], ['Message', 'Ubutumwa'], ['Send via WhatsApp →', 'Ohereza kuri WhatsApp →'],
    ['Last updated: September 2026', 'Byavuguruwe bwa nyuma: Nzeri 2026'], ['What we do', 'Ibyo dukora'], ['Why work with us', 'Impamvu wakorana natwe'],
    ['Courses can be taken individually or as a full pathway — 3D Design → Slicing → 3D-Printed Enclosure → PCB Design → CNC PCB Printing. Contact us to ask about schedules, group/school pricing, and equipment access.', 'Amasomo ashobora kwigwa ukwawo cyangwa akurikirana: 3D Design → Slicing → gukora agasanduku muri 3D → PCB Design → gucapa PCB kuri CNC. Twandikire umenye gahunda, ibiciro by’amatsinda n’uko wagera ku bikoresho.'],
    ['Local support in Musanze, reachable directly by phone or WhatsApp', 'Ubufasha buboneka i Musanze kuri telefoni cyangwa WhatsApp'], ['Project documentation written for real classroom and self-study use', 'Inyandiko z’imishinga zagenewe kwifashishwa mu ishuri no kwiyigisha'],
    ['Transparent pricing in Rwandan Francs, with Mobile Money and cash-on-delivery options', 'Ibiciro bisobanutse mu Mafaranga y’u Rwanda, wishyura kuri Mobile Money cyangwa ugezezwaho ibicuruzwa'],
    ['Get in touch', 'Twandikire'], ['Privacy Policy', 'Amabwiriza y’Umutekano w’Amakuru'], ['Terms of Service', 'Amabwiriza Agenga Imikoreshereze'],
    ['The full catalogue — every board, sensor, motor and module. Browse by category and buy at normal prices.', 'Reba boards, sensors, motors na modules zose mu byiciro, ugure ku biciro bisanzwe.'],
    ['Shop Now', 'Gura nonaha'], ['Start Learning', 'Tangira kwiga'], ['Add to Cart', 'Shyira mu Ikarita'], ['Add to cart', 'Shyira mu Ikarita'], ['View Details', 'Reba amakuru arambuye'],
    ['Out of stock', 'Byashize mu bubiko'], ['In stock', 'Birahari mu bubiko'], ['Loading…', 'Birimo gufunguka…'], ['No products found.', 'Nta bicuruzwa bibonetse.'],
    ['Continue', 'Komeza'], ['Cancel', 'Hagarika'], ['Save', 'Bika'], ['Close', 'Funga'], ['Back', 'Subira inyuma'], ['Next', 'Ibikurikira'], ['Previous', 'Ibyabanje'], ['Submit', 'Ohereza'], ['Update', 'Vugurura'], ['Remove', 'Kuraho'], ['Delete', 'Siba'], ['Copy', 'Koporora'],
    ['Search products', 'Shakisha ibicuruzwa'], ['Browse categories', 'Reba ibyiciro'], ['Featured products', 'Ibicuruzwa byatoranyijwe'], ['Choose a category', 'Hitamo icyiciro'],
    ['Current language: English. Switch to Kinyarwanda', 'Ururimi rukoreshwa: Icyongereza. Hindura mu Kinyarwanda'],
    ['Current language: Kinyarwanda. Switch to English', 'Ururimi rukoreshwa: Ikinyarwanda. Hindura mu Cyongereza'],
    ['📍 Musanze District, Rwanda', '📍 Akarere ka Musanze, Rwanda'], ['🕐 WORKING HOURS 8H – 19H30 · Closed Sun', '🕐 AMASAHA Y’AKAZI 8:00 – 19:30 · Ku Cyumweru turafunze'],
    ['Open Attendance & Stock →', 'Fungura Ubwitabire n’Ububiko →'], ['Loading Attendance & Stock…', 'Birimo gufungura Ubwitabire n’Ububiko…'],
    ['Register, practise, get feedback', 'Iyandikishe, wimenyereze, uhabwe ibitekerezo'], ['Welcome back', 'Urakaze neza'], ['Sign in', 'Injira'], ['Sign up', 'Iyandikishe'],
    ['Continue with', 'Komeza ukoresheje'], ['or continue with', 'cyangwa komeza ukoresheje'], ['Google', 'Google'], ['Microsoft', 'Microsoft'],
    ['Forgot your password?', 'Wibagiwe ijambo ry’ibanga?'], ['Password is required.', 'Ijambo ry’ibanga rirakenewe.'], ['Email is required.', 'Imeyili irakenewe.'],
    ['Create a free account', 'Fungura konti ku buntu'], ['Register now', 'Iyandikishe nonaha'], ['Already have an account?', 'Usanzwe ufite konti?'],
    ['Your profile', 'Umwirondoro wawe'], ['Save changes', 'Bika impinduka'], ['Update profile', 'Vugurura umwirondoro'], ['Order History', 'Amateka y’Ibyatumijwe'],
    ['Delivery details', 'Amakuru yo kugeza ibicuruzwa'], ['Select payment method', 'Hitamo uburyo bwo kwishyura'], ['Confirm Order', 'Emeza ibyo watumije'],
    ['Order placed successfully.', 'Ibyo watumije byakiriwe neza.'], ['Your cart is empty.', 'Ikarita yawe nta kintu kirimo.'], ['Remove item', 'Kuramo iki kintu'], ['Quantity', 'Umubare'], ['Price', 'Igiciro'],
    ['Products', 'Ibicuruzwa'], ['Shop products', 'Gura ibicuruzwa'], ['Explore products', 'Reba ibicuruzwa'], ['Showing', 'Birerekana'], ['of', 'kuri'], ['Sort by', 'Tondeka ukoresheje'],
    ['Newest', 'Ibishya'], ['Price: low to high', 'Igiciro: kuva ku gito ujya ku kinini'], ['Price: high to low', 'Igiciro: kuva ku kinini ujya ku gito'],
    ['Filter', 'Shungura'], ['Clear filters', 'Kuraho ibisabwa mu gushungura'], ['No results found', 'Nta bisubizo bibonetse'], ['Try a different search.', 'Gerageza gushakisha ukundi.'],
    ['Add to Cart', 'Shyira mu Ikarita'], ['Added to cart', 'Byashyizwe mu Ikarita'], ['View cart', 'Reba Ikarita'], ['Quick view', 'Reba vuba'], ['Product details', 'Amakuru y’igicuruzwa'],
    ['Ibiciro byagabanyijwe', 'Discounted prices'], ['Gura ibicuruzwa byatoranyijwe ku giciro cyiza mbere y’uko igihe kirangira cyangwa ibicuruzwa bikarangira mu bubiko. Ushaka ibindi?', 'Buy selected products at reduced prices before the offer ends or stock runs out. Looking for more?'],
    ['Reba ibicuruzwa byose →', 'See all products →'], ['Shaka ibikoresho by’ingirakamaro mu mishinga yawe yo guhanga no kwiga.', 'Find useful supplies for your creative and learning projects.'],
    ['Hitamo ibikoresho byagabanyirijwe ibiciro, igihe bigihari.', 'Choose discounted supplies while they are available.'],
    ['Shakisha imishinga yose yo mu bubiko n’ibicuruzwa byose byo mu iduka ahantu hamwe.', 'Search every project and every product in one place.'],
    ['Search', 'Shakisha'], ['Search by name or category', 'Shakisha ukoresheje izina cyangwa icyiciro'], ['Search projects and products', 'Shakisha imishinga n’ibicuruzwa'],
    ['About Charles Tech', 'Ibyerekeye Charles Tech'], ['Charles Tech is the trading name of CHARLES HIGH TECHNOLOGY SOLUTION Ltd (CHTS), a Rwandan company specializing in Embedded Systems and IoT Smart project solutions. We’re based in Musanze District, Rwanda.', 'Charles Tech ni izina ry’ubucuruzi rya CHARLES HIGH TECHNOLOGY SOLUTION Ltd (CHTS), ikigo nyarwanda gikora imishinga ya Embedded Systems na IoT. Gikorera mu Karere ka Musanze, Rwanda.'],
    ['We help students, hobbyists, and organizations across Rwanda build real embedded electronics and IoT projects — supplying the components, kits, tools, and step-by-step documentation needed to go from idea to working prototype. Our directory of Embedded Systems and IoT projects is built for learners at every level, from a first LED-blink circuit to full smart-monitoring systems.', 'Dufasha abanyeshuri, abakunda ikoranabuhanga n’imiryango mu Rwanda gukora imishinga nyakuri ya Embedded Electronics na IoT. Dutanga ibikoresho, kits, tools n’inyandiko zigufasha kuva ku gitekerezo ukagera kuri prototype ikora. Imishinga yacu igenewe abiga ku nzego zose.'],
    ['MANAGED FROM OUR OWN WORKSHOP', 'BIKORERWA MU KIGO CYACU'], ['What we do', 'Ibyo dukora'],
    ['Source and sell Arduino, Raspberry Pi, ESP32, sensors, and related components', 'Dushaka kandi tugurisha Arduino, Raspberry Pi, ESP32, sensors n’ibindi bikoresho bijyana na byo'],
    ['Design and document embedded systems and IoT project kits with full tools and materials lists', 'Dushushanya kandi tugasobanura kits z’imishinga ya Embedded Systems na IoT, hamwe n’urutonde rw’ibikoresho byose'],
    ['Provide guidance and adviser support for students and makers planning their own projects', 'Tugira inama abanyeshuri n’abahanga bategura imishinga yabo'],
    ['Build custom embedded/IoT solutions for organizations and individuals on request', 'Dukorera ibisubizo byihariye bya Embedded Systems na IoT amashyirahamwe n’abantu ku giti cyabo'],
    ['🎓 Courses & Training We Offer', '🎓 Amasomo n’Amahugurwa Dutanga'], ['3D Design (CAD)', 'Gushushanya muri 3D (CAD)'], ['Slicing & Print Prep', 'Gutegura dosiye yo gucapa'],
    ['3D Printing of Electronic Enclosures', 'Gucapa udusanduku tw’ibikoresho bya elegitoroniki muri 3D'], ['PCB Design', 'Gushushanya PCB'], ['PCB Printing on CNC (Finishing)', 'Gucapa PCB kuri CNC (Kurangiza)'],
    ['Software: SolidWork', 'Porogaramu: SolidWork'], ['Software: PrusaSlicer', 'Porogaramu: PrusaSlicer'], ['Software: Pronterface', 'Porogaramu: Pronterface'], ['Software: KiCad & Proteus Professional', 'Porogaramu: KiCad na Proteus Professional'],
    ['We usually reply fastest on WhatsApp during working hours. Reach us however is easiest for you:', 'Akenshi dusubiza vuba kuri WhatsApp mu masaha y’akazi. Twandikire uko bikoroheye:'],
    ['Questions about this policy can be sent to us via the Contact page.', 'Ibibazo kuri aya mabwiriza ushobora kubitwoherereza ku rupapuro rwa Twandikire.'],
    ['The site saves only your email. Use your browser’s password manager to remember your password securely.', 'Urubuga rubika imeyili yawe gusa. Koresha ububiko bw’amagambo y’ibanga bwa browser yawe kugira ngo ijambo ry’ibanga ribikwe neza.'],
    ['Loading…', 'Birimo gufunguka…'], ['Please wait…', 'Tegereza gato…'], ['Something went wrong. Please try again.', 'Habaye ikibazo. Ongera ugerageze.'],
    ['Network error. Please try again.', 'Ikibazo cy’umuyoboro. Ongera ugerageze.'], ['Saved successfully.', 'Byabitswe neza.'], ['Changes saved.', 'Impinduka zabitswe.'],
    ['Available', 'Birahari'], ['Unavailable', 'Ntibiboneka'], ['In stock', 'Birahari mu bubiko'], ['Out of stock', 'Byashize mu bubiko'],
    ['Customer information', 'Amakuru y’abakiriya'], ['Administrator monitoring', 'Igenzura ry’Ubuyobozi'], ['Search products and projects', 'Shakisha ibicuruzwa n’imishinga'],
    ['Start your project', 'Tangira umushinga wawe'], ['Contact us', 'Twandikire'], ['Talk to us', 'Tuvugishe'], ['Get in touch', 'Twandikire'], ['Send message', 'Ohereza ubutumwa'],
    ['English', 'Icyongereza'], ['Kinyarwanda', 'Ikinyarwanda'], ['Switch language', 'Hindura ururimi'],
    ['Tools & Materials Kit:', 'Ibikoresho by’Umushinga:'], ['Boards & core parts', 'Boards n’ibikoresho by’ingenzi'], ['Connectivity & displays', 'Ihuza n’ibyerekana amakuru'],
    ['Motors, power & mechanics', 'Motors, amashanyarazi n’imikorere'], ['📱 MTN MoMo & Airtel Money', '📱 MTN MoMo na Airtel Money'],
    ['Send payment directly, then enter your code at checkout', 'Ohereza amafaranga, hanyuma wandike kode yo kwishyura'], ['🏦 Equity Bank Transfer', '🏦 Kohereza kuri Equity Bank'],
    ['Embedded Projects', 'Imishinga ya Embedded Systems'], ['IoT Projects', 'Imishinga ya IoT'], ['Raspberry Pi Projects', 'Imishinga ya Raspberry Pi'],
    ['Showing', 'Birerekana'], ['Embedded', 'Embedded Systems'], ['Type', 'Andika'], ['Project Name', 'Izina ry’Umushinga'], ['Tools & Materials', 'Ibikoresho by’Umushinga'],
    ['Tip: filter by type, pick a category, or search e.g. "RFID", "GSM", or "ESP32" to narrow the list.', 'Inama: shungura ukoresheje ubwoko, hitamo icyiciro, cyangwa ushakishe nka "RFID", "GSM" cyangwa "ESP32".'],
    ['Admin:', 'Ubuyobozi:'], ['⚡ Auto-pick 8 deals (10% off, 7 days)', '⚡ Hitamo ibicuruzwa 8 (igabanyirizwa 10%, iminsi 7)'], ['🗑 Clear all deals', '🗑 Kuraho ibiciro byose byagabanyijwe'],
    ['Or open any product and use “🔥 Hot deal” to set a custom % and duration.', 'Cyangwa fungura igicuruzwa, ukoreshe “🔥 Igabanyirizwa” ushyireho ijanisha n’igihe ushaka.'],
    ['🔎 Search', '🔎 Shakisha'], ['Previous picture', 'Ifoto ibanza'], ['Next picture', 'Ifoto ikurikira'], ['Choose picture', 'Hitamo ifoto'],
    ['Previous', 'Ibyabanje'], ['Next', 'Ibikurikira'], ['Featured products', 'Ibicuruzwa by’ingenzi'], ['Hot deals', 'Ibiciro byagabanyijwe'], ['Hot Deals pictures', 'Amafoto y’ibiciro byagabanyijwe'],
    ['Learn pictures', 'Amafoto y’amasomo'], ['Featured products', 'Ibicuruzwa byatoranyijwe'], ['Filter photos by field', 'Shungura amafoto ukurikije umwuga'], ['Electronics learning photo gallery', 'Amafoto yo kwiga ibikoresho bya elegitoroniki'],
    ['All', 'Byose'], ['New', 'Gishya'], ['Sale', 'Igabanyirizwa'], ['Featured', 'Byatoranyijwe'], ['Recently added', 'Bishyizweho vuba'],
    ['Development Board Datasheets', 'Amakuru ya Development Boards'], ['Pinouts, voltage limits, and clock specs for Arduino, ESP32/ESP8266, STM32, and Raspberry Pi boards.', 'Pinout, ingano ya voltage n’umuvuduko wa Arduino, ESP32/ESP8266, STM32 na Raspberry Pi.'],
    ['Connection diagrams for ultrasonic, PIR, gas, temperature/humidity, and IMU sensors.', 'Imbonerahamwe z’uko uhuza ultrasonic, PIR, gas, ubushyuhe/ubuhehere na IMU sensors.'],
    ['Relay, motor driver, display, and communication-module reference notes with example code.', 'Inyandiko za relay, motor driver, display n’itumanaho hamwe na code y’urugero.'],
    ['Getting Coral, Jetson, and camera-based AI modules running with TensorFlow Lite / ONNX.', 'Amabwiriza yo gukoresha Coral, Jetson na camera AI modules hamwe na TensorFlow Lite / ONNX.'],
    ['Slicer profile recommendations per filament type and basic CNC setup/safety notes.', 'Imiterere ya slicer ijyanye na filament n’amabwiriza y’ibanze ya CNC n’umutekano.'],
    ['Full downloadable PDFs for each sheet above are being organised. In the meantime, search a product’s exact name on the Shop page — its detail view already lists full specifications.', 'PDF zuzuye zirategurwa. Hagati aho, shakisha izina nyaryo ry’igicuruzwa mu iduka; amakuru arambuye arimo ibisobanuro byacyo byose.'],
    ['Arduino Uno + HC-SR04 + L298N motor driver — a classic first robotics build.', 'Arduino Uno + HC-SR04 + L298N motor driver — umushinga mwiza wo gutangiriraho robotics.'],
    ['ESP32 + DHT22 sensor streaming live temperature/humidity to a phone dashboard.', 'ESP32 na DHT22 byohereza amakuru y’ubushyuhe n’ubuhehere kuri dashboard ya telefoni.'],
    ['RC522 RFID reader + servo motor + buzzer for a keycard-style access system.', 'RC522 RFID reader, servo motor na buzzer byo gukora urugi rukingurwa n’ikarita.'],
    ['Raspberry Pi + Coral Accelerator running an on-device face-detection model.', 'Raspberry Pi na Coral Accelerator zikoresha porogaramu imenyesha isura ku gikoresho.'],
    ['Capacitive soil sensor + relay module + water pump for hands-free plant watering.', 'Capacitive soil sensor, relay na pompe byuhira ibihingwa mu buryo bwikora.'],
    ['Picking wall thickness, infill, and PLA settings for a snug-fit electronics case.', 'Hitamo ubunini bw’urukuta, infill na PLA byo gucapa agasanduku k’ibikoresho bya elegitoroniki.'],
    ['Full written guides and video walkthroughs for each build above are in progress. Message us on WhatsApp for the current parts list and step order for any tutorial you’d like to start now.', 'Amabwiriza yanditse na videwo birategurwa. Twandikire kuri WhatsApp uhabwe urutonde rw’ibikoresho n’intambwe zo gutangira inyigisho ushaka.'],
    ['💬 Request a Tutorial Parts List', '💬 Saba urutonde rw’ibikoresho by’inyigisho'], ['💬 Get a Learning Roadmap on WhatsApp', '💬 Saba gahunda y’amasomo kuri WhatsApp'],
    ['Social sign-in needs a backend OAuth connection — it isn’t wired up in this preview yet, so those buttons show a heads-up rather than actually signing you in.', 'Kwinjira ukoresheje konti zo ku mbuga bisaba guhuza OAuth na backend. Muri iyi verisiyo, utubuto turakumenyesha gusa; ntiturinjiza konti.'],
    ['🔒 Privacy Policy', '🔒 Amabwiriza y’Umutekano w’Amakuru'], ['📜 Terms of Service', '📜 Amabwiriza Agenga Imikoreshereze'],
    ['By using this website or ordering from Charles Tech (CHARLES HIGH TECHNOLOGY SOLUTION Ltd), you agree to the following terms.', 'Ukoresheje uru rubuga cyangwa utumije ibicuruzwa kuri Charles Tech (CHARLES HIGH TECHNOLOGY SOLUTION Ltd), uba wemeye aya mabwiriza.'],
    ['Prices are shown in Rwandan Francs (RWF) and may change without notice. We make reasonable efforts to keep stock levels and prices accurate, but availability is confirmed at order time.', 'Ibiciro bigaragazwa mu Mafaranga y’u Rwanda (RWF) kandi bishobora guhinduka. Dukora ibishoboka ngo ibiciro n’ububiko bibe ari byo; iboneka ryemezwa igihe utumiza.'],
    ['We do not sell your personal information to third parties.', 'Ntiduha abandi bantu amakuru yawe bwite ngo bayagurishe.'],
    ['Prices are shown in Rwandan Francs (RWF).', 'Ibiciro bigaragazwa mu Mafaranga y’u Rwanda (RWF).'],
    ['No projects found.', 'Nta mishinga ibonetse.'], ['No categories found.', 'Nta byiciro bibonetse.'], ['No deals are available right now.', 'Ubu nta bicuruzwa byagabanyijwe bihari.'],
    ['Your message has been prepared in WhatsApp.', 'Ubutumwa bwawe bwateguwe muri WhatsApp.'], ['Copied to clipboard.', 'Byakoporowe.'], ['Copy failed. Please copy manually.', 'Koporora ntibyashobotse. Koresha uburyo bwo gukoporora intoki.'],
    ['Loading products…', 'Birimo gufungura ibicuruzwa…'], ['Loading projects…', 'Birimo gufungura imishinga…'], ['Loading deals…', 'Birimo gufungura ibiciro byagabanyijwe…'],
    ['Select', 'Hitamo'], ['Select a payment method', 'Hitamo uburyo bwo kwishyura'], ['Required', 'Birakenewe'], ['Optional', 'Si ngombwa'], ['(optional)', '(si ngombwa)'],
    ['Search by product name', 'Shakisha ukoresheje izina ry’igicuruzwa'], ['Show more', 'Erekana ibindi'], ['Show less', 'Erekana bike'], ['Learn more', 'Menya byinshi'], ['Read more', 'Soma ibindi'], ['See all', 'Reba byose'],
    ['Sort', 'Tondeka'], ['Reset', 'Subizaho ibya mbere'], ['Apply', 'Emeza'], ['Confirm', 'Emeza'], ['Yes', 'Yego'], ['No', 'Oya'],
    ['Item added to cart.', 'Igicuruzwa cyashyizwe mu ikarita.'], ['Removed from cart.', 'Cyakuwe mu ikarita.'], ['Please check your details.', 'Reba neza amakuru watanze.'],
    ['Order submitted.', 'Ibyo watumije byoherejwe.'], ['Thank you for your order.', 'Urakoze gutumiza.'], ['Your order is being prepared.', 'Ibyo watumije birimo gutegurwa.'],
    ['Beyond components and project kits, Charles Tech runs hands-on training that takes a learner from a 3D design on screen to a finished, working electronic enclosure and a professionally printed circuit board.', 'Uretse ibikoresho n’amapaki y’imishinga, Charles Tech itanga amahugurwa ngiro kuva ku gishushanyo cya 3D kugeza ku gasanduku k’ibikoresho bya elegitoroniki na circuit board byakozwe neza.'],
    ['Learn to model parts and enclosures from scratch in SolidWork — sketches, extrusions, assemblies, and preparing a model for 3D printing.', 'Iga gushushanya ibice n’udusanduku muri SolidWork uhereye ku ntangiriro: sketches, extrusions, assemblies no gutegura dosiye ya 3D printing.'],
    ['Convert your 3D model into printable G-code — supports, infill, layer height, and settings tuned for enclosure printing.', 'Hindura igishushanyo cya 3D mo G-code icapika; iga supports, infill, uburebure bwa layer n’imiterere yo gucapa agasanduku.'],
    ['Control and monitor your 3D printer directly, and print a complete, fitted enclosure for your electronic/IoT project.', 'Cunga kandi ukurikirane printer yawe ya 3D, ucape agasanduku gahuye n’umushinga wawe wa electronics cyangwa IoT.'],
    ['Design schematics and PCB layouts in KiCad, then simulate and verify circuit behaviour in Proteus Professional before production.', 'Shushanya schematics na PCB muri KiCad, ugerageze kandi ugenzure uko circuit ikora muri Proteus Professional mbere yo kuyikora.'],
    ['Take your finished PCB design and mill/print it on a CNC machine using Universal G-Code Sender, completing the journey from design to a physical board.', 'Fata igishushanyo cya PCB kirangiye ugicape kuri CNC ukoresheje Universal G-Code Sender, urangize kuva ku gishushanyo kugeza kuri board ifatika.'],
    ['Why work with us', 'Impamvu wakorana natwe'], ['Have a project in mind or a bulk order for a school or training center? Get in touch — we’re happy to help.', 'Ufite umushinga cyangwa ushaka ibikoresho byinshi by’ishuri cyangwa ikigo cy’amahugurwa? Twandikire tugufashe.'],
    ['1. Information we collect', '1. Amakuru dukusanya'], ['Account details', 'Amakuru ya konti'], ['Order details', 'Amakuru y’ibyo watumije'], ['Usage data', 'Amakuru y’imikoreshereze'],
    ['2. How we use it', '2. Uko tuyakoresha'], ['3. Where your data is stored', '3. Aho amakuru abikwa'], ['4. Your rights', '4. Uburenganzira ufite'], ['5. Contact', '5. Aho watwandikira'],
    ['To create and manage your account and order history', 'Gukora no gucunga konti yawe n’amateka y’ibyo watumije'],
    ['To process and deliver orders, and to contact you about them', 'Gutunganya no kugeza ibyo watumije, no kukumenyesha ibyabyo'],
    ['To respond to enquiries sent via WhatsApp, phone, or the contact form', 'Gusubiza ibibazo byatwohererejwe kuri WhatsApp, telefoni cyangwa ifomu yo kutwandikira'],
    ['Account and order data entered on this site is currently stored locally in your own browser, on this device only. We are working toward moving this to a secure server-based system; until then, this data is not shared across your devices and is not visible to us unless you send it to us directly (for example, via WhatsApp at checkout).', 'Amakuru ya konti n’ibyo watumije ubu abikwa muri browser yawe kuri iki gikoresho gusa. Turateganya kuyimurira kuri seriveri itekanye; kugeza icyo gihe, ntiyoherezwa ku bindi bikoresho kandi ntituyabona keretse uyatwoherereje, urugero kuri WhatsApp igihe wishyura.'],
    ['You may ask us at any time to tell you what information we hold about you, or to have it deleted, by contacting us on +250 787 767 610 or via WhatsApp.', 'Igihe cyose ushobora kutubaza amakuru tukubitseho cyangwa ukadusaba kuyasiba. Twandikire kuri +250 787 767 610 cyangwa kuri WhatsApp.'],
    ['1. Products & pricing', '1. Ibicuruzwa n’ibiciro'], ['2. Orders & payment', '2. Ibyatumijwe no kwishyura'],
    ['Orders are confirmed once payment is received or a valid Mobile Money transaction reference is provided at checkout. We reserve the right to cancel and refund an order we cannot fulfil.', 'Ibyatumijwe byemezwa iyo twakiriye ubwishyu cyangwa nimero yemewe ya Mobile Money. Dushobora guhagarika no gusubiza amafaranga ku byo tudashoboye gutanga.'],
    ['No account yet?', 'Nta konti uragira?'], ['Register here', 'Iyandikishe hano'], ['Already registered?', 'Warangije kwiyandikisha?'], ['Login here', 'Injira hano'],
    ['Get in touch', 'Twandikire'], ['we’re happy to help.', 'twiteguye kugufasha.'], ['Looking for more?', 'Ushaka ibindi?'], ['Create an account', 'Fungura konti'],
    ['Back to shop', 'Subira ku iduka'], ['Continue as guest', 'Komeza utarinjiye'], ['Pay now', 'Ishyura nonaha'], ['Track order', 'Kurikirana ibyo watumije'],
    ['🛒 Add to cart', '🛒 Shyira mu Ikarita'], ['🛒 Buy Now', '🛒 Gura nonaha'], ['No products in this category yet.', 'Nta bicuruzwa biri muri iki cyiciro.'],
    ['No products match this search/filter.', 'Nta bicuruzwa bihuye n’iri shakisha cyangwa gushungura.'], ['Could not save — browser storage is full. Remove some photos and try again.', 'Ntibyabitswe — ububiko bwa browser bwuzuye. Kuraho amafoto amwe wongere ugerageze.'],
    ['Remove ALL hot deals? Products go back to their regular price.', 'Kuraho ibiciro byose byagabanyijwe? Ibicuruzwa bizasubira ku biciro bisanzwe.'], ['Remove this variant?', 'Kuraho ubu bwoko bw’igicuruzwa?'],
    ['Remove this photo?', 'Kuraho iyi foto?'], ['Remove this testimonial?', 'Kuraho ubu buhamya?'], ['A product must keep at least one size', 'Igicuruzwa kigomba kugira nibura ingano imwe'],
    ['Working hours', 'Amasaha y’akazi'], ['Open today', 'Uyu munsi turafunguye'], ['Closed now', 'Ubu turafunze'], ['Musanze, Rwanda', 'Musanze, Rwanda']
  ];

  var dictionaries = { en: Object.create(null), rw: Object.create(null) };
  var kinyarwandaStart = /^(murakaza|muri|menya|kora|huza|korana|umushinga|ibitekerezo|bikwiye|iga|ukore|uhuze|wige|ubumenyi|tangirira|tangira|ntugarukire|reba|hitamo|amasomo|ibikoresho|ibiciro|igabanyirizwa|igihe|gura|shaka|shakisha|ikoranabuhanga|urutonde|amahugurwa|iby|ibyerekeye|ibicuruzwa|igiteranyo|umutekano|amabwiriza|iyandikishe|injira|umwirondoro|kwishyura|sohoka|aderesi|nimero|telefoni|uburyo|igikorwa|amafaranga|inyandiko|imbonerahamwe|imishinga|inyigisho|igiciro|tubafasha|dudufasha|dufasha|dukora|dushaka|dushushanya|dukorera|tugira|iyoborere|urubuga|akarere|amasa[h]a|dusange|funguza|fungura|funga|ohereza|andika|birimo|byashize|birahari|byose|ibyo|ibindi|sitasiyo|robot|kamera|kuhira|gucapa|gushushanya|gutegura|ubufasha|byavuguruwe|habaye|ijambo|amazina|bika|kuramo|umubare|igice|shungura|ikinyarwanda|icyongereza|nyakuri|ntabwo|ndashaka|uzigame|amapaki|vugana|twandikire)/i;
  function likelyKinyarwanda(value) {
    var text = value.replace(/^[^A-Za-z0-9]*/, '').trim();
    return kinyarwandaStart.test(text) || /^Charles High Technology Solution Ltd (ikora|ni|dufasha)/i.test(text);
  }
  languagePairs.forEach(function (pair) {
    if (likelyKinyarwanda(pair[0])) {
      dictionaries.en[pair[0]] = pair[1];
      dictionaries.rw[pair[1]] = pair[0];
    } else {
      dictionaries.en[pair[1]] = pair[0];
      dictionaries.rw[pair[0]] = pair[1];
    }
  });

  var originalText = new WeakMap();
  var originalAttributes = new WeakMap();
  var attrs = ['aria-label', 'title', 'placeholder', 'alt'];
  var currentLanguage = document.documentElement.lang === 'rw' ? 'rw' : 'en';
  var switcher = document.getElementById('siteLanguageToggle');

  function translateValue(value, language) {
    var trimmed = value.trim();
    var result = dictionaries[language][trimmed];
    if (result === undefined) return value;
    var start = value.indexOf(trimmed);
    return value.slice(0, start) + result + value.slice(start + trimmed.length);
  }

  function translateText(node, language) {
    if (!originalText.has(node)) originalText.set(node, node.nodeValue);
    node.nodeValue = translateValue(originalText.get(node), language);
  }

  function translateElementAttributes(element, language) {
    var originals = originalAttributes.get(element);
    if (!originals) { originals = Object.create(null); originalAttributes.set(element, originals); }
    attrs.forEach(function (name) {
      if (!element.hasAttribute(name)) return;
      if (!(name in originals)) originals[name] = element.getAttribute(name);
      element.setAttribute(name, translateValue(originals[name], language));
    });
  }

  function visit(node, language) {
    if (node.nodeType === Node.TEXT_NODE) {
      if (node.nodeValue.trim()) translateText(node, language);
      return;
    }
    if (node.nodeType !== Node.ELEMENT_NODE && node.nodeType !== Node.DOCUMENT_FRAGMENT_NODE) return;
    if (node.nodeType === Node.ELEMENT_NODE) {
      if (/^(SCRIPT|STYLE|NOSCRIPT|TEXTAREA|CODE|PRE)$/i.test(node.tagName)) return;
      translateElementAttributes(node, language);
    }
    var elementWalker = document.createTreeWalker(node, NodeFilter.SHOW_ELEMENT);
    var element;
    while ((element = elementWalker.nextNode())) {
      if (!/^(SCRIPT|STYLE|NOSCRIPT|TEXTAREA|CODE|PRE)$/i.test(element.tagName)) translateElementAttributes(element, language);
    }
    var walker = document.createTreeWalker(node, NodeFilter.SHOW_TEXT, {
      acceptNode: function (textNode) {
        return textNode.parentElement && textNode.parentElement.closest('script,style,noscript,textarea,code,pre')
          ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT;
      }
    });
    var textNode;
    while ((textNode = walker.nextNode())) {
      if (textNode.nodeValue.trim()) translateText(textNode, language);
    }
  }

  function applyLanguage(language) {
    currentLanguage = language === 'rw' ? 'rw' : 'en';
    window.translateSiteText = function (value) { return translateValue(String(value), currentLanguage); };
    document.documentElement.lang = currentLanguage;
    document.documentElement.dataset.language = currentLanguage;
    visit(document.body, currentLanguage);
    if (switcher) {
      switcher.setAttribute('aria-label', currentLanguage === 'en'
        ? 'Current language: English. Switch to Kinyarwanda'
        : 'Ururimi rukoreshwa: Ikinyarwanda. Hindura mu Cyongereza');
      switcher.title = currentLanguage === 'en' ? 'Switch to Kinyarwanda' : 'Hindura mu Cyongereza';
      switcher.setAttribute('aria-pressed', String(currentLanguage === 'rw'));
    }
    document.title = currentLanguage === 'en'
      ? 'Embedded Systems & IoT Courses in Musanze | Charles Tech Innovation Hub'
      : 'Amasomo ya Embedded Systems na IoT i Musanze | Charles Tech Innovation Hub';
    var description = currentLanguage === 'en'
      ? 'Build practical skills in Embedded Systems and IoT with Arduino, ESP32, sensors and real-world projects at Charles Tech in Musanze.'
      : 'Menya Embedded Systems na IoT ukoresheje Arduino, ESP32, sensors n’imishinga nyakuri muri Charles Tech i Musanze.';
    var descriptionTag = document.querySelector('meta[name="description"]');
    var socialDescription = document.querySelector('meta[property="og:description"]');
    if (descriptionTag) descriptionTag.content = description;
    if (socialDescription) socialDescription.content = description;
    try { localStorage.setItem('chts-language', currentLanguage); } catch (e) {}
  }

  if (switcher) switcher.addEventListener('click', function () {
    applyLanguage(currentLanguage === 'en' ? 'rw' : 'en');
  });

  ['alert', 'confirm'].forEach(function (name) {
    var nativeDialog = window[name];
    window[name] = function (message) {
      return nativeDialog.call(window, translateValue(String(message), currentLanguage));
    };
  });

  applyLanguage(currentLanguage);
  var observer = new MutationObserver(function (records) {
    records.forEach(function (record) {
      record.addedNodes.forEach(function (node) { visit(node, currentLanguage); });
    });
  });
  observer.observe(document.body, { childList: true, subtree: true });
})();
