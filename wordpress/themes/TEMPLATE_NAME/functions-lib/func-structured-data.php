<?php

/**
 * 構造化データ（JSON-LD）の出力
 */

// 構造化データ（JSON-LD）の出力
function add_structured_data()
{
	$structured_data = array();

	// Organization（組織情報）- 全ページ共通
	$organization = array(
		'@context' => 'https://schema.org',
		'@type' => 'Organization',
		'name' => '高松産業株式会社',
		'url' => home_url('/'),
		'logo' => array(
			'@type' => 'ImageObject',
			'url' => get_template_directory_uri() . '/images/logo_company.svg'
		)
	);
	$structured_data[] = $organization;

	// WebSite（サイト情報）- トップページのみ
	if (is_front_page()) {
		$website = array(
			'@context' => 'https://schema.org',
			'@type' => 'WebSite',
			'name' => get_bloginfo('name'),
			'url' => home_url('/'),
			'description' => get_bloginfo('description'),
			'publisher' => array(
				'@type' => 'Organization',
				'name' => '高松産業株式会社'
			)
		);
		$structured_data[] = $website;
	}

	// FAQPage（よくある質問）- FAQページとリクルートページ
	if (is_page('faq') || is_page('recruit')) {
		// FAQページ用の項目
		if (is_page('faq')) {
			// 静的なFAQ項目（フォールバック用）
			$faq_items = array(
				array(
					'question' => '個人向けに店頭販売はしていますか？',
					'answer' => '店頭にて小売販売もしております。日々、多くの方が取寄せてほしい品や、探してほしい部品などについて相談にお見えになっています。最小単位での購入も可能ですので、お気軽にお立ち寄りください。遠方の方は、代引きにて商品の発送も承ります。'
				),
				array(
					'question' => '修理などの受け付けはしてもらえますか？',
					'answer' => '電動工具や測定機器など、弊社の取り扱いしているメーカーであればご相談に乗らせていただきます。まずはお電話等でお問い合わせください。'
				),
				array(
					'question' => 'バッテリーなどのリサイクルや、工具の再研磨はしていますか？',
					'answer' => 'バッテリーの種類によってできないものもありますが、リサイクル可能な商品が増えていますので、一度お問合せください。また、工具の再研磨、再コーティング加工もできます。納期は、弊社へお持込いただければ約2～3週間でお受け取りができます。'
				),
				array(
					'question' => '「香川どてらい市」に行きたいのですが、どのように登録すればいいでしょうか？',
					'answer' => '「香川どてらい市」は、毎年9月第一金曜日と土曜日の2日間にわたり、「サンメッセ香川」にて開催されます。7月中旬から下旬ごろに弊社にご来店いただければ、「入場券」をお渡ししますので、ぜひご来場ください。また近く成りましたら、ぜひお問い合わせいただければ幸いです。'
				),
				array(
					'question' => '高松産業で展示会などは開催されていますか？',
					'answer' => '毎春、弊社にて「プライベートショー」を開催しています。その年ごとにテーマを決めて、商社さまやメーカーさまの商品を実際に展示販売し、皆さまにご紹介しております。毎年たくさんのお客様にご来場いただき、反響の高いイベントとなっております。隔年で本社と西部での開催となりますので、ぜひご覧になりたい方は、高松産業までお問い合わせください。'
				)
			);
		}
		// リクルートページ用のFAQ項目
		else if (is_page('recruit')) {
			$faq_items = array(
				array(
					'question' => '入社前に取得しておくべき資格はありますか？',
					'answer' => '採用の際に、資格の有無は問いません。業務上必要な資格がある場合は、入社後に取得していただく形となります。'
				),
				array(
					'question' => '入社時期は相談できますか？',
					'answer' => '中途採用の場合、入社時期などは面接時にご希望をうかがい、相談しながら決定いたします。'
				),
				array(
					'question' => '残業や休日出勤はありますか？',
					'answer' => '残業時間は従事する業務によって異なりますが、平均15時間～20時間程度です。弊社は土曜日も半日会社を開けておりますので、5週間に一回程度、土曜日当番が回ってきます。また、年に一回、どてらい市という大きな展示会が、9月の第一週目の金曜日と土曜日に開催されるのですが、その際は社員全員が参加となります。そのほか、お客さまのご要望に合わせて、休日出勤になる場合もあります。'
				)
			);
		}

		// FAQ構造化データの構築
		$mainEntity = array();
		foreach ($faq_items as $item) {
			$mainEntity[] = array(
				'@type' => 'Question',
				'name' => $item['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text' => $item['answer']
				)
			);
		}

		$faq_data = array(
			'@context' => 'https://schema.org',
			'@type' => 'FAQPage',
			'mainEntity' => $mainEntity
		);
		$structured_data[] = $faq_data;
	}

	// JobPosting（募集要項）- 募集要項ページのみ
	if (is_page('requirements')) {
		// 会社情報
		$company = array(
			'@type' => 'Organization',
			'name' => '高松産業株式会社',
			'url' => home_url('/')
		);

		// 本社 営業職の求人情報
		$job_posting_honsha = array(
			'@context' => 'https://schema.org',
			'@type' => 'JobPosting',
			'title' => '本社 営業職',
			'description' => '約1年倉庫内仕事・配達業を経験した後、得意先を回り、営業に従事',
			'datePosted' => date('Y-m-d'),
			'validThrough' => date('Y-m-d', strtotime('+6 months')),
			'employmentType' => 'FULL_TIME',
			'hiringOrganization' => $company,
			'jobLocation' => array(
				'@type' => 'Place',
				'address' => array(
					'@type' => 'PostalAddress',
					'addressLocality' => '高松市',
					'addressRegion' => '香川県',
					'postalCode' => '760-0063',
					'streetAddress' => '多賀町三丁目18-1',
					'addressCountry' => 'JP'
				)
			),
			'baseSalary' => array(
				'@type' => 'MonetaryAmount',
				'currency' => 'JPY',
				'value' => array(
					'@type' => 'QuantitativeValue',
					'minValue' => 180000,
					'maxValue' => 220000,
					'unitText' => 'MONTH'
				)
			),
			'educationRequirements' => array(
				'@type' => 'EducationalOccupationalCredential',
				'credentialCategory' => 'high school'
			),
			'workHours' => '8:30～17:30',
			'benefits' => '通勤手当（限度額16,000円/月）、社会保険等、雇用保険、労災保険、健康保険、厚生年金保険、財形貯蓄制度、育児休業制度あり、自家用車通勤可',
			'schedule' => array(
				'@type' => 'Schedule',
				'byDay' => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
				'workHours' => '8:30-17:30'
			),
			'jobBenefits' => '毎週土曜日※月1回 8：30～12：30 当番有り、日曜・祝日完全休、夏季休暇・年末年始休暇有・有給休暇・慶弔休暇',
			'applicationContact' => array(
				'@type' => 'ContactPoint',
				'contactType' => 'HR',
				'description' => '書類選考・筆記試験・面接による採用プロセス'
			)
		);
		$structured_data[] = $job_posting_honsha;

		// 西部営業所 営業職の求人情報
		$job_posting_seibu = array(
			'@context' => 'https://schema.org',
			'@type' => 'JobPosting',
			'title' => '西部営業所 営業職',
			'description' => '約1年倉庫内仕事・配達業を経験した後、得意先を回り、営業に従事',
			'datePosted' => date('Y-m-d'),
			'validThrough' => date('Y-m-d', strtotime('+6 months')),
			'employmentType' => 'FULL_TIME',
			'hiringOrganization' => $company,
			'jobLocation' => array(
				'@type' => 'Place',
				'address' => array(
					'@type' => 'PostalAddress',
					'addressLocality' => '善通寺市',
					'addressRegion' => '香川県',
					'postalCode' => '765-0061',
					'streetAddress' => '吉原町7-1',
					'addressCountry' => 'JP'
				)
			),
			'baseSalary' => array(
				'@type' => 'MonetaryAmount',
				'currency' => 'JPY',
				'value' => array(
					'@type' => 'QuantitativeValue',
					'minValue' => 180000,
					'maxValue' => 220000,
					'unitText' => 'MONTH'
				)
			),
			'educationRequirements' => array(
				'@type' => 'EducationalOccupationalCredential',
				'credentialCategory' => 'high school'
			),
			'workHours' => '8:30～17:30',
			'benefits' => '通勤手当（限度額16,000円/月）、社会保険等、雇用保険、労災保険、健康保険、厚生年金保険、財形貯蓄制度、育児休業制度あり、自家用車通勤可',
			'schedule' => array(
				'@type' => 'Schedule',
				'byDay' => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
				'workHours' => '8:30-17:30'
			),
			'jobBenefits' => '毎週土曜日※月1回 8：30～12：30 当番有り、日曜・祝日完全休、夏季休暇・年末年始休暇有・有給休暇・慶弔休暇',
			'applicationContact' => array(
				'@type' => 'ContactPoint',
				'contactType' => 'HR',
				'description' => '書類選考・筆記試験・面接による採用プロセス'
			)
		);
		$structured_data[] = $job_posting_seibu;
	}

	// BreadcrumbList（パンくずリスト）- トップページ以外
	if (!is_front_page() && function_exists('custom_get_breadcrumb_items')) {
		// 自作パンくず関数からパンくず配列を取得
		$breadcrumb_items = custom_get_breadcrumb_items();

		if (!empty($breadcrumb_items)) {
			$itemListElement = array();
			$position = 1;

			foreach ($breadcrumb_items as $item) {
				// URLが空の場合は現在のページのURLを設定
				if (empty($item['url']) && isset($item['current']) && $item['current']) {
					$item['url'] = get_permalink(); // 現在のページのURL
				}

				// URLが空でない場合のみ追加（TOPページなどは必ずURLがある）
				if (!empty($item['url'])) {
					$listItem = array(
						'@type' => 'ListItem',
						'position' => $position,
						'name' => $item['title'],
						'item' => array(
							'@type' => 'WebPage',
							'@id' => $item['url']
						)
					);

					$itemListElement[] = $listItem;
					$position++;
				}
			}

			if (!empty($itemListElement)) {
				$breadcrumb_data = array(
					'@context' => 'https://schema.org',
					'@type' => 'BreadcrumbList',
					'itemListElement' => $itemListElement
				);
				$structured_data[] = $breadcrumb_data;
			}
		}
	}

	// JSON-LD形式で出力
	if (!empty($structured_data)) {
		echo '<script type="application/ld+json">' . PHP_EOL;
		echo json_encode($structured_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
		echo '</script>' . PHP_EOL;
	}
}
add_action('wp_head', 'add_structured_data', 20);
