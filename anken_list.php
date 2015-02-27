<?php
class Anken{
	//大分類
	public $kbn1;
	//小分類
	public $kbn2;
	//案件名(事業年度・名称)
	public $ankenName;
	//実施機関
	public $agency;
	//リンク
	public $link;
	// 落札業者
	public $company;
	// 落札金額
	public $price;
}
class AnkenListForm{
	// title
	public $siteTitle = '';
	// link
	public $siteUrl = 'http://ce50h7/nyusatsu_check_rss/anken_list.php';
	// description
	public $siteDescription = '';
	// category
	public $siteCategory = '';
	// generator
	public $siteGenerator = 'me :-)';
	// webMaster
	public $adminMailAddr = 'tomoyuki@brains.info';
	
	public $ankenList = array();
}

$state = $_GET['state'];
$kind = $_GET['kind'];

$form = new AnkenListForm();

// RSSのヘッダー作成
if($state==='pub' && $kind==='gene'){
	// 現在公開分 => 一般競争入札
	$form->siteTitle = '01_現在公開分：一般競争入札';
	$form->siteDescription = '原則として、落札予定価格が一定の金額（委託料では１００万円）を超えるものが対象です。指定の入札日時・場所において入札書を提出していただき、落札を決定します。';
	$form->siteCategory = '（条件付き）一般競争入札';
}elseif($state==='pub' && $kind==='easy'){
	// 現在公開分 => 簡易公開調達
	$form->siteTitle = '02_簡易公開調達：現在公開分';
	$form->siteDescription = '一般競争入札を行うもの以外が対象です。指定の〆切日時までに、指定の場所に見積書を提出していただき、落札を決定します。';
	$form->siteCategory = '簡易公開調達';
}elseif($state==='end' && $kind==='gene'){
	// 現在公開分 => 一般競争入札
	$form->siteTitle = '03_既に終了分：一般競争入札';
	$form->siteDescription = '原則として、落札予定価格が一定の金額（委託料では１００万円）を超えるものが対象です。指定の入札日時・場所において入札書を提出していただき、落札を決定します。';
	$form->siteCategory = '（条件付き）一般競争入札';
}elseif($state==='end' && $kind==='easy'){
	// 現在公開分 => 簡易公開調達
	$form->siteTitle = '04_簡易公開調達：既に終了分';
	$form->siteDescription = '一般競争入札を行うもの以外が対象です。指定の〆切日時までに、指定の場所に見積書を提出していただき、落札を決定します。';
	$form->siteCategory = '簡易公開調達';
}else{
	die('不正なパラメータです。');
}

$link = pg_connect('host=localhost dbname=nyusatsu_check user=nyusatsu_check password=nyusatsu_check');
if(! $link){
	die('接続失敗です。'.pg_last_error());
}

if($state==='pub' && $kind==='gene'){
	$sql = 'SELECT * FROM v_latest_tenders where keishu_cd = 1 and public_flag = 0';
}elseif($state==='pub' && $kind==='easy'){
	$sql = 'SELECT * FROM v_latest_tenders where keishu_cd = 2 and public_flag = 0';
}elseif($state==='end' && $kind==='gene'){
	$sql = 'SELECT * FROM v_latest_tenders where keishu_cd = 1 and public_flag = 1';
}elseif($state==='end' && $kind==='easy'){
	$sql = 'SELECT * FROM v_latest_tenders where keishu_cd = 2 and public_flag = 1';
}else{
	die('不正なパラメータです。');
}

$result = pg_query($sql);
if(! $result){
	die('クエリーが失敗しました。'.pg_last_error);
}
for($i = 0; $i < pg_num_rows($result); $i++){
	$row = pg_fetch_array($result, null, PGSQL_ASSOC);
	// echo a;
	$anken = new Anken();
	
	// 業務大分類
	$anken->kbn1 = $row['gyoumu_kbn_1'];
	// 業務小分類
	$anken->kbn2 = $row['gyoumu_kbn_2'];
	// 案件名(事業年度・名称)
	$anken->ankenName = $row['anken_name'];
	// 実施機関
	$anken->agency = $row['kasitu_name'];
	// 対象URL
	$query = array('q' => $row['anken_no']);
	$anken->link = 'http://ce50h7/nyusatsu_check_view/search/search.php?'.http_build_query($query);
	// 落札業者名等
	$anken->company = $row['raku_name'];
	// 落札金額（税込・円）
	$anken->price = $row['price'];

	array_push($form->ankenList, $anken);
}
pg_close($link);

?>
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
	<channel>
		<title><?php echo $form->siteTitle ?></title>
		<link><?php echo $form->siteUrl ?></link>
		<description><?php echo $form->siteDescription ?></description>
		<language>ja</language>
		<category><?php echo $form->siteCategory ?></category>
		<generator><?php echo $form->siteGenerator ?></generator>
		<webMaster><?php echo $form->adminMailAddr ?></webMaster>

		<?php foreach($form->ankenList as $anken): ?>
		<item>
			<title><?php echo $anken->kbn1.' '.$anken->kbn2 ?></title>
			<author><?php echo $anken->agency ?></author>
			<link><?php echo $anken->link ?></link>
			<description><?php echo $anken->ankenName.' '.$anken->company.' '.$anken->price ?></description>
			<category><?php echo $anken->kbn1 ?></category>
		</item>
		<?php endforeach; ?>

	</channel>
</rss>