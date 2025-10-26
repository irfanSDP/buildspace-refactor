<?php

use PCK\Clauses\Clause;
use PCK\Contracts\Contract;

class ClauseItemsTableSeeder extends Seeder {

	/**
	 * Auto generated seed file
	 *
	 * @return void
	 */
	public function run()
	{
		$main              = new Clause;
		$main->contract_id = Contract::findByType(Contract::TYPE_PAM2006)->id;
		$main->name        = Clause::TYPE_MAIN_TEXT;
		$main->save();

		$lAndE              = new Clause;
		$lAndE->contract_id = Contract::findByType(Contract::TYPE_PAM2006)->id;
        $lAndE->name         = Clause::TYPE_LOSS_AND_EXPENSES_TEXT;
		$lAndE->save();

		$ae              = new Clause;
		$ae->contract_id = Contract::findByType(Contract::TYPE_PAM2006)->id;
        $ae->name      = Clause::TYPE_ADDITIONAL_EXPENSES_TEXT;
		$ae->save();

		$eot              = new Clause;
		$eot->contract_id = Contract::findByType(Contract::TYPE_PAM2006)->id;
        $eot->name       = Clause::TYPE_EXTENSION_OF_TIME_TEXT;
		$eot->save();

		\DB::table('clause_items')->insert(array(
			0   =>
				array(
					'clause_id'   => $main->id,
					'no'          => '1.1                      ',
					'description' => 'Completion of Works in accordance with Contract Documents: The contractor shall upon and subject to these Conditions carry out and complete the Works in accordance with the Contract Documents and in compliance therewith in provide materials, goods and standards of workmanship of the quality and standard described in the Contract Documents and/or required by the Architect in accordance with the provisions of the Contract.',
					'priority'    => 0,
					'created_at'  => '2014-09-30 15:21:32.803658',
					'updated_at'  => '2014-09-30 15:21:32.803658',
				),
			1   =>
				array(
					'clause_id'   => $main->id,
					'no'          => '1.2                      ',
					'description' => 'Temporary work and construction method: Unless designed by the Architect or Consultant, the Contractor shall be fully responsible for the adequacy, suitability and safety of all temporary works and of all methods of construction of the Works, irrespective of any approval by the Architect or Consultant.',
					'priority'    => 1,
					'created_at'  => '2014-09-30 15:21:32.814138',
					'updated_at'  => '2014-09-30 15:21:32.814138',
				),
			2   =>
				array(
					'clause_id'   => $main->id,
					'no'          => '1.3                      ',
					'description' => 'Contractor’s design and responsibilities: If the Contractor proposes any alternative design to that specified in the Works or if the Contract leaves any matter of design, specification or choice of materials, goods and workmanship to the Contractor, the Contractor shall ensure that such works are fit for its purpose. The copyright of the Contractor’s design and alternative design belongs to the Contractor, but the Employer shall be entitled to use the design and alternative design for completion, maintenance, repair and future extension of the Works. The acceptance by the Architect or Consultant of the Contractor’s design and alternative design shall not relieve the Contractor of his responsibilities under the Contract.',
					'priority'    => 2,
					'created_at'  => '2014-09-30 15:21:32.815982',
					'updated_at'  => '2014-09-30 15:21:32.815982',
				),
			3   =>
				array(
					'clause_id'   => $main->id,
					'no'          => '1.4                      ',
					'description' => 'Discrepancy or divergence between documents: The Contractor shall use the Contract Documents and any other subsequent documents issued by the Architect to plan the Works prior to execution. If during the said planning and subsequent execution of the Works, the contractor finds any discrepancy in or divergence between any of the Contract Documents and any subsequent documents issued by the Architect, he shall give to the Architect a written notice in sufficient time before the commencement of construction of the affected works, specifying the discrepancy or divergence to enable the Architect to issue written instructions within a period which would not materially delay the progress of the affected works, having regard to the Completion Date. Such discrepancy or divergence shall not vitiate the Contract. ',
					'priority'    => 3,
					'created_at'  => '2014-09-30 15:21:32.816924',
					'updated_at'  => '2014-09-30 15:21:32.816924',
				),
			4   =>
				array(
					'clause_id'   => $main->id,
					'no'          => '2.1                      ',
					'description' => 'Contractor to comply with AI: The Contractor shall subject to Clauses 2.2 and 2.3 forthwith comply with all instructions issued to him by the Architect in regard to any matter in respect of which the Architect is expressly empowered by these Conditions to issue instructions.',
					'priority'    => 4,
					'created_at'  => '2014-09-30 15:21:32.817786',
					'updated_at'  => '2014-09-30 15:21:32.817786',
				),
			5   =>
				array(
					'clause_id'   => $main->id,
					'no'          => '2.2(a)                   ',
					'description' => 'All instruction issued by the Architect shall be in writing expressly entitled “Architect’s Instruction” (‘AI’). All other forms of written instructions including drawing issued by the Architect shall be an AI upon written confirmation from the Contractor entitled “Confirmation of Architect’s Instruction” (‘CAI’); or',
					'priority'    => 5,
					'created_at'  => '2014-09-30 15:21:32.818639',
					'updated_at'  => '2014-09-30 15:21:32.818639',
				),
			6   =>
				array(
					'clause_id'   => $main->id,
					'no'          => '2.2(b)                   ',
					'description' => 'All instruction issued by the Architect shall be in writing expressly entitled “Architect’s Instruction” (‘AI’). All other forms of written instructions including drawing issued by the Architect shall be an AI upon subsequent confirmation of the written instructions by the Architect with an AI',
					'priority'    => 6,
					'created_at'  => '2014-09-30 15:21:32.819576',
					'updated_at'  => '2014-09-30 15:21:32.819576',
				),
			7   =>
				array(
					'clause_id'   => $main->id,
					'no'          => '2.3                      ',
					'description' => 'Provisions empowering instructions: Upon receipt of a written instruction from the Architect, the Contractor may request the Architect to specify in writing which provision of these Conditions empowers the issuance of the said instruction and the Architect shall forthwith comply with such a request. If the Contractor thereafter complies with the said instruction without invoking any dispute resolution procedure under the Contract to establish the Architect’s power in that regard, the instruction shall be deemed to have been duly given under the specified provision. ',
					'priority'    => 7,
					'created_at'  => '2014-09-30 15:21:32.820421',
					'updated_at'  => '2014-09-30 15:21:32.820421',
				),
			8   =>
				array(
					'clause_id'   => $main->id,
					'no'          => '2.4                      ',
					'description' => 'Failure of Contractor to comply with AI: If the time of compliance [which shall not be less than seven (7) Days from receipt of the AI] is stated by the Architect in the AI and the Contractor does not comply therewith then the Employer may, without prejudice to any other rights and remedies which he may possess under the Contract, employ and pay other Person to execute any work which may be necessary to give effect to such instruction. The cost of employing other Person and any additional cost in this connection shall be set-off by the Employer under clause 30.4.',
					'priority'    => 8,
					'created_at'  => '2014-09-30 15:21:32.821175',
					'updated_at'  => '2014-09-30 15:21:32.821175',
				),
			9   =>
				array(
					'clause_id'   => $main->id,
					'no'          => '3.1                      ',
					'description' => 'Contract Documents: The Contract Documents are to be read as mutually explanatory of one another. In the event of any conflict or inconsistencies between any of the Contract Document, the priority in the interpretation of such documents shall be in the following descending order: 3.1(a) the Letter of Award; 3.1(b) the Articles of Agreement; 3.1 (c) the Conditions of Contract; 3.1 (d) the Contract Drawings; 3.1 (e) the Contract Drawings; and 3.1 (f) other documents incorporated in the Contract Documents, unless expressly stated to be excluded in any of the Contract Document.',
					'priority'    => 9,
					'created_at'  => '2014-09-30 15:21:32.821868',
					'updated_at'  => '2014-09-30 15:21:32.821868',
				),
			10  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '3.2                      ',
					'description' => 'Custody of tender documents: The original tender documents shall remain in the custody of the Architect or Quantity Surveyor so as to be available at all reasonable times for inspection by the Employer or Contractor.',
					'priority'    => 10,
					'created_at'  => '2014-09-30 15:21:32.822554',
					'updated_at'  => '2014-09-30 15:21:32.822554',
				),
			11  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '3.3                      ',
					'description' => 'Copies of documents: Immediately after the execution of the Contract, the Architect or Quantity Surveyor shall without charge to the Contractor provide him with: 3.3 (a) one of the two signed original copies of the Contract Documents; 3.3 (b) two (2) further copies of the Contract Drawings; and 3.3 (c) two (2) copies of the unpriced Contract Bills.',
					'priority'    => 11,
					'created_at'  => '2014-09-30 15:21:32.823234',
					'updated_at'  => '2014-09-30 15:21:32.823234',
				),
			12  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '3.4                      ',
					'description' => 'Further drawings or details: When necessary, the Architect shall without charge to the Contractor furnish him with two (2) copies of further drawings, details, levels and any other information as are reasonably necessary either to explain and amplify the Contract Drawings or to enable the Contractor to complete the Works in accordance with these Conditions. If the Contractor requires any further drawings, details, levels and any other information, he shall specifically apply in writing to the Architect for these items in sufficient time before the commencement of construction of the affected works to enable the Architect to issue instruction within a period which would not materially delay the progress of the affected works having regard to the Completion Date.',
					'priority'    => 12,
					'created_at'  => '2014-09-30 15:21:32.82395',
					'updated_at'  => '2014-09-30 15:21:32.82395',
				),
			13  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '13.1                     ',
					'description' => 'Contract Sum not to be adjusted or altered: The Contract Sum shall not be adjusted or altered to any way whatsoever, other than in accordance with the express provisions of the contract. Any arithmetical errors or any errors in the prices and rates shall be corrected and/or rationalized by the Architect or Consultant without any change to the Contract Sum before the signing of Contract. ',
					'priority'    => 52,
					'created_at'  => '2014-09-30 15:21:32.852684',
					'updated_at'  => '2014-09-30 15:21:32.852684',
				),
			14  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '3.5                      ',
					'description' => 'Works Programme: Within twenty one (21) Days from receipt of the Letter of Award (or within such longer period as may be agreed in writing by the Architect), the Contractor shall provide to the Architect for his information, six (6) copies of the Works Programme (unless a higher number is stated in the Contract Documents) showing the order in which he proposes to carry out the Works. The Works Programme shall comply with any requirements specified in the Contract Documents. If the Works or any part of the Works is delayed for whatever reason, the Architect may instruct the Contractor to revise the Works Programme. The Contractor without charge to the Employer, shall provide the Architect from time to time with similar number of copies of any revised Works Programme.',
					'priority'    => 13,
					'created_at'  => '2014-09-30 15:21:32.824846',
					'updated_at'  => '2014-09-30 15:21:32.824846',
				),
			15  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '3.6                      ',
					'description' => 'Programme not part of Contract: The Works Programme shall not constitute part of the Contract, whether physically incorporated or not into the Contract Documents.',
					'priority'    => 14,
					'created_at'  => '2014-09-30 15:21:32.825919',
					'updated_at'  => '2014-09-30 15:21:32.825919',
				),
			16  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '3.7                      ',
					'description' => 'Architect’s acceptance of programme: The acceptance by the Architect of the Works Programme shall not relieve the Contractor of his obligations, duties or responsibilities under the Contract. The Work Programme may be used by the Architect to monitor progress and the Architect is entitled to rely on the Works Programme as a basis for the assessment of extension of time and the effect of the delay and/or disturbances to the progress of the Works.',
					'priority'    => 15,
					'created_at'  => '2014-09-30 15:21:32.82662',
					'updated_at'  => '2014-09-30 15:21:32.82662',
				),
			17  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '3.8                      ',
					'description' => 'Availability of documents: The Contractor shall keep a copy of the Contract Drawings and the unpriced Contract Bills on the Site to be available to the Architect and Consultant and their authorized representatives at all reasonable times.',
					'priority'    => 16,
					'created_at'  => '2014-09-30 15:21:32.827322',
					'updated_at'  => '2014-09-30 15:21:32.827322',
				),
			18  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '3.9                      ',
					'description' => 'Limitation of use of documents: None of the Contract Documents in Clause 3.1 shall be used by the Contractor for any purpose other than the Contract. Except for the purpose of the Contract, the parties shall not disclose any of the rates and prices in the Contract Bills to any other party.',
					'priority'    => 17,
					'created_at'  => '2014-09-30 15:21:32.827996',
					'updated_at'  => '2014-09-30 15:21:32.827996',
				),
			19  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '3.10                     ',
					'description' => 'As-built Drawings and operation and maintenance manuals: The Contractor shall supply and shall cause any Nominated Sub-Contractor to supply As-built Drawings and/or operation and maintenance manuals specified in the Contract Documents and/or operation and maintenance manuals specified in the Contract Documents and/or Nominated Sub-Contract documents in the manner and within the time specified therein. Where these are not specified, the Contractor shall supply and shall ensure the Nominated Sub-Contractor supplies four (4) copies of the above items before the Completion Date.',
					'priority'    => 18,
					'created_at'  => '2014-09-30 15:21:32.828672',
					'updated_at'  => '2014-09-30 15:21:32.828672',
				),
			20  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '4.1                      ',
					'description' => 'Statutory requirements: The Contractor shall comply with and submit all notices required by any laws, regulations, by-laws, terms and conditions of any Appropriate Authority and Service Provider in respect of the execution of the Works and all temporary works.',
					'priority'    => 19,
					'created_at'  => '2014-09-30 15:21:32.829351',
					'updated_at'  => '2014-09-30 15:21:32.829351',
				),
			21  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '4.2                      ',
					'description' => 'Inconsistencies with statutory requirements: If the Contractor finds any inconsistencies between the Contract Documents (including any subsequent documents issued by the Architect) and any laws, regulations, by-laws, terms and conditions of any Appropriate Authority and Service Provider, he shall immediately specify the inconsistencies and give to the Architect a written notice before commencement of construction of the affected works.',
					'priority'    => 20,
					'created_at'  => '2014-09-30 15:21:32.830008',
					'updated_at'  => '2014-09-30 15:21:32.830008',
				),
			22  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '4.3                      ',
					'description' => 'Conforming to statutory obligations: If within seven (7) Days of having given written notice to the Architect, the Contractor does not receive any AI in regard to the matters specified in Clause 4.2, he shall proceed with the work to conform to such laws, regulations, by-laws, terms and conditions of any Appropriate Authority and Service Provider. Any changes so necessitated shall be deemed to be a Variation required by the Architect.',
					'priority'    => 21,
					'created_at'  => '2014-09-30 15:21:32.830672',
					'updated_at'  => '2014-09-30 15:21:32.830672',
				),
			23  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '4.4                      ',
					'description' => 'Fees, levies and charges: The Contractor shall pay and indemnify the Employer against any liability I respect of any fees, levies and charges including any penalties which may arise from the Contractor’s non-compliance with any laws, regulations, by-laws, terms and conditions of any Appropriate Authority and Service Provider in respect of the execution of the Works and all temporary works. If the Contractor fails to pay, the Employer may pay such amount and such amount together with any additional cost in this connection shall be set-off by the Employer under Clause 30.4.',
					'priority'    => 22,
					'created_at'  => '2014-09-30 15:21:32.831344',
					'updated_at'  => '2014-09-30 15:21:32.831344',
				),
			24  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '5.1                      ',
					'description' => 'Setting out : The Architect shall determine all levels which may be required for the execution of the Works and shall provide the Contractor with drawings and information to enable the Contractor to set out the Works. The Contractor shall at his own cost rectify any errors arising from any inaccurate setting out. With the consent of the Employer, the Architect may instruct that such errors need not be rectified subject to an appropriate deduction to be set-off by the Employer under Clause 30.4.',
					'priority'    => 23,
					'created_at'  => '2014-09-30 15:21:32.832033',
					'updated_at'  => '2014-09-30 15:21:32.832033',
				),
			25  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '6.1                      ',
					'description' => 'Standards of works, materials, goods and workmanship: All works, materials, goods and workmanship shall be of the respective quality and standards described in the Contract Documents and required by the Architect in accordance with the provisions of the Contract.',
					'priority'    => 24,
					'created_at'  => '2014-09-30 15:21:32.832741',
					'updated_at'  => '2014-09-30 15:21:32.832741',
				),
			26  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '6.2                      ',
					'description' => 'Provision of vouchers: The Contractor shall upon the request of the Architect, provide him with vouchers or such other evidence to prove that the materials and goods comply with Clause 6.1',
					'priority'    => 25,
					'created_at'  => '2014-09-30 15:21:32.833429',
					'updated_at'  => '2014-09-30 15:21:32.833429',
				),
			27  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '6.3                      ',
					'description' => 'Inspection and testing: The Contractor shall provide samples of materials and goods for testing before incorporation into the Works. The Architect may issue an AI requiring the Contractor to open up for inspection any work covered up, or to arrange for or carry out any test on any materials and goods already incorporated in the Works of any executed work. The cost of such opening up or testing together with the cost of making good shall be added to the Contract Sum unless: 6.3 (a) the cost is provided for in the Contract Bills; 6.3 (b) the inspection or test shows that the works, materials and goods were not in accordance with the Contract; or 6.3 (c) the inspection or test was in the opinion of the Architect required in consequence of some prior negligence, omission, default and/or breach of contract by the Contractor.',
					'priority'    => 26,
					'created_at'  => '2014-09-30 15:21:32.834086',
					'updated_at'  => '2014-09-30 15:21:32.834086',
				),
			28  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '6.4                      ',
					'description' => 'Contractor’s obligation not relieved: The provisions of Clauses 6.2 and 6.3 shall not relieve the Contractor of his obligations to execute the work and supply materials and goods in accordance with the Contract.',
					'priority'    => 27,
					'created_at'  => '2014-09-30 15:21:32.834778',
					'updated_at'  => '2014-09-30 15:21:32.834778',
				),
			29  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '14.1                     ',
					'description' => 'Materials and goods not to be removed: Materials and goods delivered to the Site for incorporation into permanent works shall not be removed until completion of the Works unless prior consent in writing from the Architect has been obtained, which consent shall not be unreasonably withheld or delayed.',
					'priority'    => 53,
					'created_at'  => '2014-09-30 15:21:32.853348',
					'updated_at'  => '2014-09-30 15:21:32.853348',
				),
			30  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '6.5                      ',
					'description' => 'Works not in accordance with the Contract: If the Architect finds any work, materials, goods or workmanship which is not in accordance with the Contract, the Architect shall instruct the Contractor in writing: 6.5 (a) to remove from and not to bring to the Site such materials and goods; 6.5 (b) to demolish and reconstruct such work to comply with the Contract; 6.5 (c) to rectify such work as instructed by the Architect with no adjustment to Contract Sum; 6.5 (d) to submit a method state within seven (7) Days from receipt of the written instruction (or within such period as may be specified by the Architect in the instruction) proposing how such works, materials, goods, or workmanship can be rectified. If the Architect accepts the Contractor’s proposal, the Contractor shall carry out the rectification work with no adjustment to the Contract Sum or alternatively, the Architect may reject the proposal and issues any other written instruction under this clause; or 6.5(e) with the consent of the Employer, to leave all or any such works, materials, goods or workmanship in the Works subject to an appropriate set-off by the Employer under Clause 30.4 and the Contractor shall remain liable for the same.',
					'priority'    => 28,
					'created_at'  => '2014-09-30 15:21:32.835524',
					'updated_at'  => '2014-09-30 15:21:32.835524',
				),
			31  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '6.6                      ',
					'description' => 'No compensation for time and cost: Compliance by the Contractor with a written instruction issued under Clause 6.5 shall not entitle the Contractor to an extension of time nor compensation for any loss and/or expense that may be incurred.',
					'priority'    => 29,
					'created_at'  => '2014-09-30 15:21:32.836375',
					'updated_at'  => '2014-09-30 15:21:32.836375',
				),
			32  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '6.7                      ',
					'description' => 'Failure of Contractor to comply: If the Contractor fails or refuses to comply with a written instruction of the Architect issued under Clause 6.5, the Employer may without prejudice to any other rights or remedies which he may possess under the contract, employ and pay other Person to carry out the subject matter of the written instruction. All costs incurred including any loss and expense shall be set-off by the Employer under Clause 30.4.',
					'priority'    => 30,
					'created_at'  => '2014-09-30 15:21:32.837056',
					'updated_at'  => '2014-09-30 15:21:32.837056',
				),
			33  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '6.8                      ',
					'description' => 'Warranties in respect of materials and goods: If the Contract requires any manufacturer, sub-contractor or supplier to give a warranty or guarantee in respect of any proprietary systems, materials and goods supplied, the contractor shall procure such warranties or guarantees and submit to the Employer. The provision of such warranties or guarantees shall in no way relieve or release the Contractor from any liabilities under the Contract.',
					'priority'    => 31,
					'created_at'  => '2014-09-30 15:21:32.837729',
					'updated_at'  => '2014-09-30 15:21:32.837729',
				),
			34  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '7.1                      ',
					'description' => 'Indemnity to Employer: Subject to Clause 7.2, all royalties or other sums payable in respect of the supply and use in carrying out the Works of any articles, processes, inventions or drawings shall be deemed to have been included in the Contract Sum. The Contractor shall indemnify the Employer against all claims, proceedings, damages, costs and expenses which may be brought against the Employer or to which he may be subjected to by reason of the Contractor infringing or being held to have infringed any such intellectual property rights.',
					'priority'    => 32,
					'created_at'  => '2014-09-30 15:21:32.8384',
					'updated_at'  => '2014-09-30 15:21:32.8384',
				),
			35  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '7.2                      ',
					'description' => 'Contractor’s liability to pay: Where in compliance with a written instruction, the Contractor has informed the Architect in writing that there may be an infringement of intellectual property rights but the Architect still instructs the Contractor in writing to comply, the Contractor shall not be liable for any such infringement. All royalties, damages or other monies which the Contractor may be liable to pay for such infringement shall be added to the Contract Sum.',
					'priority'    => 33,
					'created_at'  => '2014-09-30 15:21:32.839074',
					'updated_at'  => '2014-09-30 15:21:32.839074',
				),
			36  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '7.3                      ',
					'description' => 'Government royalties: Except where otherwise provided for in the Contract, the Contractor shall pay all Government royalties, levies, rent and all other payments in connection with the Works.',
					'priority'    => 34,
					'created_at'  => '2014-09-30 15:21:32.839754',
					'updated_at'  => '2014-09-30 15:21:32.839754',
				),
			37  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '8.1                      ',
					'description' => 'Site Agent: The Contractor shall appoint a competent person to be the Site Agent. The Site Agent for the purposes of the Contract Shall be deemed to be the Contractor’s authorized site representative. The site Agent shall be assisted by such assistants and supervisory staff as necessary to execute the works efficiently and satisfactorily. The Site Agent shall be employed full time on Site and in the event that he has to be temporarily absent from the Site, the Contractor shall designate a deputy in his place.',
					'priority'    => 35,
					'created_at'  => '2014-09-30 15:21:32.840436',
					'updated_at'  => '2014-09-30 15:21:32.840436',
				),
			38  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '8.2                      ',
					'description' => 'Instruction to Site Agent: The Contractor shall ensure that the Site Agent and such assistants and supervisory staff are capable of receiving directions or instructions in English or Bahasa Malaysia. The Site Agent shall be deemed to be authorized by the Contractor to receive any directions given by the Site Staff or instructions given by the Architect and any such directions and instructions given shall be deemed to have been given to the Contractor.',
					'priority'    => 36,
					'created_at'  => '2014-09-30 15:21:32.841117',
					'updated_at'  => '2014-09-30 15:21:32.841117',
				),
			39  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '8.3                      ',
					'description' => 'Exclusion of Person employed on the Works: The Architect may instruct the Contractor to remove the Site Agent or any Person under the employment or control of the Contractor from the Site. The Architect shall not exercise this discretion unreasonably or vexatiously. On receipt of a written instruction, the contractor shall immediately remove and replace such staff or any Person within a reasonable time and such staff or Person so removed, shall not again be employed on Site. The Contractor shall not be entitled to any extension of time and additional cost in respect of any instruction given by the Architect under this clause.',
					'priority'    => 37,
					'created_at'  => '2014-09-30 15:21:32.841797',
					'updated_at'  => '2014-09-30 15:21:32.841797',
				),
			40  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '9.1                      ',
					'description' => 'Access to the Works: The Architect, consultant and their authorised representatives shall at all times have reasonable access to the Works and to the factories, workshop ot other places where any construction plan, materials, goods and work are being fabricated, prepared or stored for the Contract. The Contractor shall ensure that all sub-contractors contain provisions entitling the Architect, Consultant and their authorised representatives to have such access.',
					'priority'    => 38,
					'created_at'  => '2014-09-30 15:21:32.842485',
					'updated_at'  => '2014-09-30 15:21:32.842485',
				),
			41  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '10.1                     ',
					'description' => 'Duty of Site Staff: The Employer may from time to time appoint such number of Site Staff as the Employer shall deem necessary. The Site Staff shall act as inspection under the direction of the Architect and the contractor shall provide reasonable facilities for the performance of such duties. ',
					'priority'    => 39,
					'created_at'  => '2014-09-30 15:21:32.843159',
					'updated_at'  => '2014-09-30 15:21:32.843159',
				),
			42  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '10.2                     ',
					'description' => 'Directions given by Site Staff: Any direction given to the Contractor or his Site Agent by the Site Staff shall be of no effect, unless given in writing in regard to a matter in respect of which the Site Staff have been expressly authorised in writing by the Architect. All such direction involving a Variation shall be of no effect, unless confirmed by an AI.',
					'priority'    => 40,
					'created_at'  => '2014-09-30 15:21:32.843841',
					'updated_at'  => '2014-09-30 15:21:32.843841',
				),
			43  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '14.2                     ',
					'description' => 'Materials and goods included in certificates: Where the value of such materials and goods has in accordance with Clause 30.2 been included in any Interim Certificate under which the Employer has effected payment, such materials and goods shall become the property of the Employer.',
					'priority'    => 54,
					'created_at'  => '2014-09-30 15:21:32.854037',
					'updated_at'  => '2014-09-30 15:21:32.854037',
				),
			44  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '14.3                     ',
					'description' => 'Responsibility for materials and goods: The Contractor shall be responsible for any loss and/or damage to such materials and goods including materials and goods supplied by Nominated Sub-Contractors and Nominated Suppliers.',
					'priority'    => 55,
					'created_at'  => '2014-09-30 15:21:32.8547',
					'updated_at'  => '2014-09-30 15:21:32.8547',
				),
			45  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '11.1                     ',
					'description' => 'Definition of Variation: The term “Variation” means the alteration or modification of the design, quality or quantity of the Works including: 11.1 (a) the addition, omission or substitution of any work, 11.1 (b) the alteration of the kind or standard of any materials and goods to be used in the Works, 11.1 (c) the removal from the Site of any work executed or materials and goods brought thereon by the contractor for the purposes of the Works other than work, materials and goods which are not in accordance with the Contract, and, 11.1(d) any changes to the provisions in the Contract with regards to: 11.1(e) (i) any limitation of working hours; 11.1(d) (ii) working spaces; 11.1(d) (iii) access to or utilization of any specific part of the Site; and 11.1(d) (iv) the execution and completion of the work in any specific order, but shall exclude any changes intended to rectify any negligence, omissions, default and/or contract by the Contractor and such changes shall be executed by the Contractor entirely at his own cost.',
					'priority'    => 41,
					'created_at'  => '2014-09-30 15:21:32.844514',
					'updated_at'  => '2014-09-30 15:21:32.844514',
				),
			46  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '11.2                     ',
					'description' => 'No Variations required by Architect shall vitiate Contract: The Architect may issue an AI ordering a variation or sanctioning any Variations made by the Contractor. No variation ordered by the Architect or subsequently sanctioned by him shall vitiate the Contract. Pending the valuation of the Variations, the Contractor shall carry out with due diligence and expedition all Variations so instructed.',
					'priority'    => 42,
					'created_at'  => '2014-09-30 15:21:32.845326',
					'updated_at'  => '2014-09-30 15:21:32.845326',
				),
			47  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '11.3                     ',
					'description' => 'Issue of Variations after Practical Completion: The Architect may issue instructions in writing requiring a Variation at any time before the issuance of the Certificate of Practical completion. Thereafter, any AI requiring a Variation must be necessitated by obligations or compliance with the requirements of any Appropriate Authority and Services provider. ',
					'priority'    => 43,
					'created_at'  => '2014-09-30 15:21:32.846035',
					'updated_at'  => '2014-09-30 15:21:32.846035',
				),
			48  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '11.4                     ',
					'description' => 'AI on P.C. Sums and Provisional Sums: The Architect shall issue AI n regard to the expenditure of P.C. Sums and Provisional Sums included in the Contract Bills and of P.C. Sums which arise as a result of instructions issued in regard to the expenditure of Provisional Sums.',
					'priority'    => 44,
					'created_at'  => '2014-09-30 15:21:32.846703',
					'updated_at'  => '2014-09-30 15:21:32.846703',
				),
			49  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '11.5                     ',
					'description' => 'Valuation of Variations and Provisional Sums: All Variations shall be measured and valued by the Quantity Surveyor. Where any recording of site information and / or site measurements are carried out at the Site, the Contractor shall provide the Quantity surveyor with such assistance as may be necessary to carry out the works and the Contractor shall be given the opportunity to be present to take such notes and measurements as he may require.',
					'priority'    => 45,
					'created_at'  => '2014-09-30 15:21:32.847365',
					'updated_at'  => '2014-09-30 15:21:32.847365',
				),
			50  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '11.6                     ',
					'description' => 'Valuation rules: The valuation of Variations and work executed by the Contractor for which a Provisional Quantity is included in the Contract and the expenditure of Provisional Sums (other than for work for which a tender had been accepted under Clause 27.14) shall be made in accordance with the following rules: 11.6(a) where work is of a similar character to, is executed under similar conditions as, and does not significantly change the quantity of work as set out in the Contract Documents, the rates and prices in the Contract Documents shall determine the valuation; 11.6 (b) where work is of a similar character to work as set out in the Contract Documents but is not executed under similar conditions or is executed under similar conditions but there is significant change in the quantity of work carried out, the rates and prices in the Contract Documents shall be the basis for determining the valuation which shall include a fair adjustment in the rates to take into account such difference. 11.6 (c) where work is not of a similar character to work as set out in the Contract Documents, the valuation shall be at fair market rates and prices determined by the Quantity Surveyor; 11.6 (d) where work cannot be properly measured and valued in accordance with Clause 11.6(a), (b) or (c), the Contractor shall be allowed: 11.6(d) (i) the daywork rates in the Contract Documents; or 11.6(d) (ii) where there are no such daywork rates in the Contract Documents, at the actual cost to the Contractor of his materials, additional construction plant and scaffolding, transport and labour for the work concerned, plus fifthteen (15) percent, which percentage shall include for the use of all tools, standing plant, standing scaffolding, supervision, overheads and profit. In either case, vouchers specifying the time spent daily upon the work, the workers names, materials, additional construction plant, scaffolding and transport used shall be signed by the Site Agent and verified by the Site Staff and shall be delivered to the Architect and Quantity Surveyor at weekly intervals with the final records delivered not later than fourteen (14) Days after the work has been completed. 11.6(e) the rates and prices in the Contract documents shall determine the valuation of items omitted. If omissions substantially vary the conditions under which any remaining items of work are carried put, the prices of such remaining items shall be valued under Clause 11.6 (a), (b) or (c); and 11.6(f) in respect of Provisional Quantity, the quantities stated in the Contract Documents shall be re-measured by the Quantity Surveyor based on the actual quantities executed. The rates and prices in the Contract Documents shall determine their valuations.',
					'priority'    => 46,
					'created_at'  => '2014-09-30 15:21:32.848085',
					'updated_at'  => '2014-09-30 15:21:32.848085',
				),
			51  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '11.7                     ',
					'description' => 'Additional expense caused by Variation: Where a variation has caused or is likely to cause the Contractor to incur additional expenses for which he would not be paid under any provisions in Clause 11.6, the Contractor may make a claim for such additional expenses provided always that: 11.7(a) the Contractor shall give written notice to the Architect of his intention to claim for such additional expenses together with an initial estimate of his claim duly supported with all necessary calculations. Such notice must be given within twenty eight (28) Days from the date of the AI or CAI giving rise to his claim. The giving of such written notice shall be a condition precedent to any entitlement to additional expenses that the Contractor may have under the Contract; and 11.7(b) within twenty eight (280 Days of completing such variation, the Contractor shall send to the Architect and Quantity Surveyor complete particulars of his claim for additional expenses together with all necessary calculations to substantiate his claims. If the Contractor fails to submit the required particulars within the stated time (or within such longer period as may be agreed in writing by the Architect), it shall be deemed that the Contractor has waived his rights to any such additional expenses. ',
					'priority'    => 47,
					'created_at'  => '2014-09-30 15:21:32.849228',
					'updated_at'  => '2014-09-30 15:21:32.849228',
				),
			52  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '11.8                     ',
					'description' => 'Access to Contactor’s books and documents: The Contractor shall keep contemporaneous records to substantiate all his claims for additional expenses under Clause 11.7, and shall submit all particulars to the Architect and Quantity Surveyor. The Architect and Quantity Surveyor shall have access to all books, documents, reports, papers or records in the possession, custody or control of the Contractor that are material to the claim and the Contractor shall provide free of charge a copy each to the Architect and Quantity Surveyor when requested. All such documents shall remain available in accordance with this clause until all claims have been resolved. The Contractor shall use his best endeavour to ensure that all such similar documents in the possession, custody or control of sub-contractor and/or suppliers that are material to the claim are similarly available.',
					'priority'    => 48,
					'created_at'  => '2014-09-30 15:21:32.849977',
					'updated_at'  => '2014-09-30 15:21:32.849977',
				),
			53  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '11.9                     ',
					'description' => 'Variations and additional expenses added to Contract Sum: As soon as the Architect has ascertained the amount of Variations and/or additional expenses claimed by the Contractors under Clause 11.7, the amount so ascertained shall be added to the Contract Sum. When an Interim Certificate is issued after the date of ascertainment, such amount shall be included in the certificate.',
					'priority'    => 49,
					'created_at'  => '2014-09-30 15:21:32.850687',
					'updated_at'  => '2014-09-30 15:21:32.850687',
				),
			54  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '12.1                     ',
					'description' => 'Measurement of building works: The quality and quantity of work included in the Contract Sum shall be deemed to be those which are set out in the Contract Bills and unless otherwise expressly stated, shall be prepare in accordance with the principle of the standard method of Measurement of Building Works sanctioned by the Institution of Surveyor Malaysia and currently in force.',
					'priority'    => 50,
					'created_at'  => '2014-09-30 15:21:32.851364',
					'updated_at'  => '2014-09-30 15:21:32.851364',
				),
			55  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '12.2                     ',
					'description' => 'Correction of errors or omissions: Unless otherwise expressly provided, the contract is a Lump Sum Contract. Any error in description, quantity or omission of items in the Contract Bills shall not vitiate the Contract and shall be corrected by the Architect or Consultant.',
					'priority'    => 51,
					'created_at'  => '2014-09-30 15:21:32.852026',
					'updated_at'  => '2014-09-30 15:21:32.852026',
				),
			56  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '14.4                     ',
					'description' => 'Warranty of title of goods and materials: The Contractor shall be deemed to have warranted that he has title free from encumbrances for such materials and goods upon inclusion of the value of such materials and goods in any applications for payment under Clause 30.1. in the event that the Contractor is found to have made a false warranty, any loss suffered by the Employer shall be made good by the Contractor or shall be set-off under Clause 30.4.',
					'priority'    => 56,
					'created_at'  => '2014-09-30 15:21:32.855355',
					'updated_at'  => '2014-09-30 15:21:32.855355',
				),
			57  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '15.1                     ',
					'description' => 'Practical Completion: The Work are Practically Completed when: 15.1(a) in the opinion of the Architect, the Employer can have full use of the Works for their intended purposes, notwithstanding that there may be works and defects of a minor nature still to be executed and the Contractor has given to the Architect a written undertaking to make good and to complete such works and defects within a reasonable time specified by the Architect; and 15.1(b) other requirements expressly stated in the Contract Documents as a pre-requisite for the issuance of the Certificate of Practical Completion have been complied with.',
					'priority'    => 57,
					'created_at'  => '2014-09-30 15:21:32.856062',
					'updated_at'  => '2014-09-30 15:21:32.856062',
				),
			58  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '15.2                     ',
					'description' => 'Certificate of Practical Completion: When the whole of the Works are Practically Completed, the Contractor shall forthwith give written notice to that effect to the Architect who shall within fourteen (14) Days do either one of the following: 15.2(a) if the Architect is of the opinion that the Works are not Practically Completed under Clause 15.1, the Architect shall give written notice to the Contractor with copy extended to the Nominated Sub-Contractors stating the reasons for his opinion; or 15.2 (b) if the Architect is of the opinion that the Works are Practically Completed under Clause 15.1, the Architect shall issue the Certificate of Practical Completion. The date of Practical Completion shall be: 15.2(b) (i) the date of receipt of the Contractor’s written undertaking to make good and to complete works and defects of a minor nature, where there are such works and defects; or 15.2(b) (ii) the date of receipt of the Contractor’s written notice, where there are no works and defects of a minor nature. The Certificate of Practical Completion shall be issued to the Contractor with copies extended to the Employer and Nominated Sub-Contractors. Upon the issuance of Certificate of Practical Completion by the Architect, the Contractor shall forthwith return Site possession to the Employer.',
					'priority'    => 58,
					'created_at'  => '2014-09-30 15:21:32.856764',
					'updated_at'  => '2014-09-30 15:21:32.856764',
				),
			59  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '15.3                     ',
					'description' => 'Contractor’s failure to comply with undertaking: Where applicable, the Contractor shall comply with his undertaking to attend to the works and defects of a minor nature under Clause 15.1(a) within the specified time. In the event the Contractor fails to comply with his undertaking, the Employer may without prejudice to any other rights and remedies which he may possess under the Contract do any one of the following: 15.3(a) grant the Contractor additional ex-gratia time to be specified by the Architect to enable the Contractor to comply with his said undertaking; 15.3(b) employ and pay other Person to execute any work which may be necessary to give effect to the Contractor’s said undertaking. All costs incurred including any loss and/or expense shall be set-off by the Employer under Clause 30.4; or 15.3(c) accept to leave all or any such works and defects of a minor nature in the Works subject to an appropriate set-off under Clause 30.4.',
					'priority'    => 59,
					'created_at'  => '2014-09-30 15:21:32.857503',
					'updated_at'  => '2014-09-30 15:21:32.857503',
				),
			60  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '15.4                     ',
					'description' => 'Schedule of Defects: Any Defects in the Works which appear within the Defects Liability Period shall be specified by the Architect in a schedule of defects which he shall deliver to the Contractor not later than fourteen (14) Days after the expiration of the Defects Liability Period. The Contractor shall make good the Defects specified within twenty eight (28) Days after receipt of the schedule of defects (or within such longer period as may be agreed in writing by the Architect) at the Contractor’s cost. If the Contractor fails to attend to the Defects, the Employer may, without prejudice to any other rights and remedies which he may possess under the Contract, employ and pay other Person to rectify the Defects and all costs incurred shall be set-off by the Employer under Clause 30.4. If the Architect with the consent of the Employer, instructs the Contractor to leave the Defects in the Works, then an appropriate deduction for such Defects not made good by the Contractor shall be set-off by the Employer under Clause 30.4.',
					'priority'    => 60,
					'created_at'  => '2014-09-30 15:21:32.858222',
					'updated_at'  => '2014-09-30 15:21:32.858222',
				),
			61  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '15.5                     ',
					'description' => 'Instruction to make good Defects: Notwithstanding Clause 15.4, the Architect may at any time during the Defects Liability Period issue an AI requiring any critical Defects which need urgent rectification to be made good within a reasonable time specified by the Architect at the Contractor’s cost. If the Contractor fails to attend to such Defects within the time specified by the Architect, the Employer may employ and pay other Person to rectify such Defects and all costs incurred shall be set-off by the Employer under Clause 30.4.',
					'priority'    => 61,
					'created_at'  => '2014-09-30 15:21:32.858953',
					'updated_at'  => '2014-09-30 15:21:32.858953',
				),
			62  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '15.6                     ',
					'description' => 'Certificate of Making Good Defects: Upon completion of making good all Defects which may have been required to be made good under Clause 15.4, the Contractor shall forthwith give written notice to the Architect to that effect. The Architect shall within fourteen (14) Days do either one of the following: 15.6(a) if the Architect is of the opinion that there is no Defects or the Contractor has made good all Defects, the Architect shall issue a Certificate of Making Good Defects and the date of making good Defects shall be the date of receipt of the Contractor’s written notice. The Certificate of Making Good Defects shall be issued to the Contractor and copies shall be extended to the Employer and Nominated Sub-Contractors; or 15.6 (b) if the Architect is of the opinion that the Defects have not been made good, the Architect shall give written notice to the Contractor with copies to Nominated Sub-Contractors stating the reasons for the non-issuance of the Certificate of Making Good Defects.',
					'priority'    => 62,
					'created_at'  => '2014-09-30 15:21:32.859654',
					'updated_at'  => '2014-09-30 15:21:32.859654',
				),
			63  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '16.1                     ',
					'description' => 'Possession of Occupied Part with consent: If at any time before Practical Completion of the Works, the Employer wishes to take possession and occupy any part of the Works (“the with Occupied Part”) and the consent of the Contractor (whose consent shall not be unreasonably delayed or withheld) has been obtained, then notwithstanding anything expressed or implied elsewhere in the Contract, the Employer may take possession of the Occupied Part and the following shall apply: 16.1(a) within fourteen (14) Days from the date on which the Employer has taken possession of the Occupied Part, the Architect shall issue a Certificate of Partial Completion. The Certificate of Partial Completion shall state the Architect’s estimate of the approximate total value of the Occupied Part and for all purposes of Clause 16.0, the value so stated shall be deemed to be the total value of the Occupied Part; 16.1(b) for the purposes of Clauses 15.4, 15.5 and 16.1(f), Practical Completion of the Occupied Part shall be deemed to have occurred and the Defects Liability Period in respect of the Occupied Part shall be deemed to have commenced on the date which the Employer has taken possession; 16.1(c) the Liquidated Damages under Clauses 22.1 shall be reduced by the ratio of the estimated value of the Occupied Part to the Contract Sum; 16.1(d) upon the issuance of the Certificate of Partial Completion, the Architect shall within fourteen (14) Days issue a certificate to release half the amount of the Retention Fund in the ratio of the estimated value of the Occupied Part to the Contract Sum. The Contractor shall be entitled to payment within the Period of Honouring Certificate. The amount of the Limit of Retention Fund shall then be reduced by the same amount; 16.1(e) when in the opinion of the Architect all Defects in the Occupied Part which he may have required to be made good under Clause 15.4 or 15.5 have been made good, he shall issue a Certificate of Making Good Defects under Clause 15.6 in respect of the Occupied Part; and 16.1(f) upon the issuance of the Certificate of Making Good Defects of the Occupied Part, the Architect shall within fourteen (14) Days issue a certificate for the release of the remaining amount of the Retention Fund in respect of the Occupied Part. The Contractor shall be entitled to payment within the Period of Honouring Certificate.',
					'priority'    => 63,
					'created_at'  => '2014-09-30 15:21:32.860414',
					'updated_at'  => '2014-09-30 15:21:32.860414',
				),
			64  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '16.2                     ',
					'description' => 'Possession of Occupied Part without consent: The Employer may, without prejudice to any other rights and remedies which he may possess under the Contract, enter and occupy such part of the Works prior to the completion of the whole of the Works without the consent of the Contractor under Clause 16.1 provided always that: 16.2(a) the completion of the Works has been delayed and a Certificate of Non-Completion has been issued by the Architect under Clause 22.1; and 16.2(b) such entry and occupation of the Occupied Part can be effected without any unreasonable disturbance to the progress of the Contractor’s remaining works. In that event, the provision of Clauses 16.1(a) to 16.1(f) shall apply.',
					'priority'    => 64,
					'created_at'  => '2014-09-30 15:21:32.861341',
					'updated_at'  => '2014-09-30 15:21:32.861341',
				),
			65  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '16.3                     ',
					'description' => 'Contractor to remove equipment: If the Employer takes possession of the Occupied Part under Clause 16.1 or 16.2, the Contractor shall upon the written instruction of the Architect remove his site facilities, construction plant or equipment, materials and goods from the Occupied Part.',
					'priority'    => 65,
					'created_at'  => '2014-09-30 15:21:32.862087',
					'updated_at'  => '2014-09-30 15:21:32.862087',
				),
			66  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '17.1                     ',
					'description' => 'Assignment by Employer: Other than assigning his rights, interests or benefits under the Contract to his financial institution, the Employer shall not without the written consent of the Contractor (such consent shall not be reasonably delayed or withheld) assign the same to other parties.',
					'priority'    => 66,
					'created_at'  => '2014-09-30 15:21:32.862751',
					'updated_at'  => '2014-09-30 15:21:32.862751',
				),
			67  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '17.2                     ',
					'description' => 'Assignment by Contractor: Other than assigning any payment due or to become due under the Contract to his financial institution, the Contractor shall not without the written consent of the Employer (such consent shall not be at the sole discretion of the Employer) assign his rights, interests or benefits under the Contract to other parties.',
					'priority'    => 67,
					'created_at'  => '2014-09-30 15:21:32.863413',
					'updated_at'  => '2014-09-30 15:21:32.863413',
				),
			68  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '17.3                     ',
					'description' => 'No sub-contracting: Except where otherwise provided by the Contractor, the Contractor shall not wholly or substantially sub-contract the Works. Where the Contractor sub-contracts labour only of craftsmen, skilled or semi-skilled workmen to carry out any portion of the Works, this shall not constitute sub-contracting within the meaning of this clause.',
					'priority'    => 68,
					'created_at'  => '2014-09-30 15:21:32.864072',
					'updated_at'  => '2014-09-30 15:21:32.864072',
				),
			69  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '18.1                     ',
					'description' => 'Contractor’s indemnity against injury or death: The Contractor shall be liable for and shall indemnify the Employer against any damage, expense, liability, loss, claim or proceedings whatsoever whether arising at common law or by statute in respects of personal injury to or death of any person arising out of or in the course of or caused by the carrying out of the Works and provided always that the same is due to any negligence, omission, default and/or breach of contract by the Contractor or of any Person for whom the Contractor is responsible.',
					'priority'    => 69,
					'created_at'  => '2014-09-30 15:21:32.864736',
					'updated_at'  => '2014-09-30 15:21:32.864736',
				),
			70  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '18.2                     ',
					'description' => 'Contractor’s indemnity against loss and/or damage: The Contractor shall be liable for and shall indemnify the Employer against any damage, expense, liability, loss, claim or proceedings due to loss and/or damage of any kind whatsoever to any property real or personal, including the Works and any other property of the Employer, in so far as such loss and/or damage arises out of or in the course of or  by reason of the execution of the Works and provided always that the same is due to any negligence, omission, default and/or breach of contract by the Contractor or of any Person for whom the Contractor is responsible. ',
					'priority'    => 70,
					'created_at'  => '2014-09-30 15:21:32.865435',
					'updated_at'  => '2014-09-30 15:21:32.865435',
				),
			71  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '18.3                     ',
					'description' => 'Contractor’s indemnity against claims by workmen: The Contractor shall be liable for and shall indemnify the Employer against any damage, expense, liability, loss, claim or proceedings whatsoever arising out of claims by any and every workmen employed in and for the execution of the Works and for payment of compensation under or by virtue of the Workmen’s Compensation Act 1952 and the Employees’ Social Security Act 1969. ',
					'priority'    => 71,
					'created_at'  => '2014-09-30 15:21:32.866126',
					'updated_at'  => '2014-09-30 15:21:32.866126',
				),
			72  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '18.4                     ',
					'description' => 'Indemnities not to be defeated: The indemnities given by the Contractor under clauses 18.1 to 18.3 shall not be defeated or reduced by reason of any negligence or omission of the Employer, Architect, Consultant or other authorized representatives in failing to supervise or control the Contractor’s site operation or methods of working or temporary work or to detect or prevent or remedy defective work or to ensure proper performance of any obligation of the Contractor under the Contract. ',
					'priority'    => 72,
					'created_at'  => '2014-09-30 15:21:32.866831',
					'updated_at'  => '2014-09-30 15:21:32.866831',
				),
			73  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '19.1                     ',
					'description' => 'Contractor insure against injury to Person and loss and/or damage of property: Without prejudice to his liability to indemnify the Employer under Clause 18.0, the Contractor shall, as a condition precedent to the commencement of any work under the Contract, take out and maintain in the joint names of the Employer, Contractor, sub-contractor and all interested parties in respect of personal injuries or death and injury or loss and/or damage of property real or personal arising out of or in the course of or by reason of the execution of the Works and whether or not such injury, death, loss and/or damage is caused by negligence, omission, default and/or breach of contract by the Contractor, Employer, sub-contractor and interested parties and any of their servants and agents. Such insurance policy shall provide cover in respect of third party liability for personal injury or death and damage to property for the amounts stated in the Appendix. If the Contractor having regard to his indemnity to the Employer under Clause 18.0 desires to increase any of the insurance coverage, he shall do so and allow for any additional cost. The insurance policy shall include the following endorsements: 19.1(a) a “cross liability” endorsement to provide insurance cover to the Employer and Contractor and any other parties involved in the Works as though they are separately insured for their respective rights and interest; 19.1(b) an endorsement to the effect that the Architect, Consultant and any other professional consultant (as applicable) and their employees and representatives, Site Staff, employees and representatives of the Employer, are deemed to be third parties; 19.1(c) an endorsement for wavier of all expressed or implied rights of subrogation or recoveries against the insured; and 19.1(d) an endorsement for automatic extension or renewal of the insurance up to the insurance of the Certificate of Making Good Defects.',
					'priority'    => 73,
					'created_at'  => '2014-09-30 15:21:32.867527',
					'updated_at'  => '2014-09-30 15:21:32.867527',
				),
			74  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '19.2                     ',
					'description' => 'Employees’ social security scheme for local workmen: Without prejudice to his liability to indemnify the Employer under Clause 18.0, the Contractor shall register or cause to register all local workmen employed on the Works and who are subject to registration under the Employees’ Social Security Scheme (hereinafter referred to as “SOCSO”) in accordance with the Employees’ Social Security Act 1969 and shall cause all sub-contractors to comply with the same provisions. The Contractor shall make payment of all contributions and cause all sub-contractors to make similar payments from time to time when the same ought to be paid.',
					'priority'    => 74,
					'created_at'  => '2014-09-30 15:21:32.868312',
					'updated_at'  => '2014-09-30 15:21:32.868312',
				),
			75  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '19.3                     ',
					'description' => 'Insurance for local workmen not subject to SOCSO: Without prejudice to his liability to indemnify the Employer under Clause 18.0, the Contractor shall, as a condition precedent to the commencement of any work under the Contract, take out and maintain in the joint names of the Employer and Contractor and shall cause all sub-contractors to take out and maintain a similar insurance policy for local workmen who are not subject to registration under SOCSO. Such insurance policy shall be effected and maintained as necessary to cover all liabilities including common law liability in respect of any claim which may arise in the course of the execution of the Works. The insurance policy shall be valid up to the Completion Date and the extended maintenance cover shall be for the Defects Liability Period plus a further three (3) Months. If the Contractor is unable to complete by the Completion Date or complete making good the Defects within the insured period, he shall ensure that the insurance is accordingly extended for the same period of delay. The Contractor shall effect the said extension of the insurance cover not less than one (1) Month before the expiry of the insurance currently in force.',
					'priority'    => 75,
					'created_at'  => '2014-09-30 15:21:32.869006',
					'updated_at'  => '2014-09-30 15:21:32.869006',
				),
			76  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '19.4                     ',
					'description' => 'Workmen’s compensation insurance for foreign workers: Without prejudice to his liability to indemnify the Employer under Clause 18.0, the Contractor shall, as a condition precedent to the commencement of any work under the Contract, take out and maintain in the name of the Contractor and shall cause all sub-contractors to take out and maintain a similar insurance policy for all foreign workers employed on the Works as required by the Workmen’s Compensation Act 1952 and Workmen’s Compensation (Foreign Worker’s Compensation Scheme)(Insurance) Order 1998. Such insurance policy shall be effected and maintained as necessary to cover all liabilities including common law liability in respect of any claim which may arise in the course of the execution of the Works. The insurance policy shall be valid up to the Completion Date and the extended maintenance cover shall be for the Defects Liability Period plus a further three (3) Months. If the Contractor is unable to complete by the Completion Date or complete making good the Defects within the insured period, he shall ensure that the insurance is accordingly extended for the same period of delay. The Contractor shall effect the said extension of the insurance cover not less than one (1) Month before the expiry of the insurance currently in force. ',
					'priority'    => 76,
					'created_at'  => '2014-09-30 15:21:32.869775',
					'updated_at'  => '2014-09-30 15:21:32.869775',
				),
			77  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '19.5                     ',
					'description' => 'Placing of insurance with licensed insurance companies: The insurance referred to in Clauses 19.1, 19.2, 19.3 and 19.4 shall be placed with licensed insurance companies approved by the Employer, and the Contractor shall deposit with the Employer the policy and the receipt of premiums paid with copies extended to the Architect and Consultant. If the Contractor makes default in insuring or continuing to insure as aforesaid, the Employer may (but is not obligated to) insure against any risks in respect of which the default has occurred and the amount of premiums and any other cost incurred or paid by the Employer shall be set-off by the Employer under Clause 30.4.',
					'priority'    => 77,
					'created_at'  => '2014-09-30 15:21:32.870507',
					'updated_at'  => '2014-09-30 15:21:32.870507',
				),
			78  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.A.1                   ',
					'description' => 'Contractor’s risks – new buildings/ works: Without prejudice to his liability to indemnify the Employer under Clause 18.0, the Contractor shall, as a condition precedent to the commencement of any work under the Contract, take out and maintain in the joint names of the Employer, Contractor, sub-contractors and all interested parties a CAR Insurance policy for a value not less than the Contract Sum, plus the sum to cover professional fees for reinstatement and the sum to cover the removal of debris all as stated in the Appendix. Unless covered by the standard CAR Insurance policy, the insurance shall have endorsements to cover against loss and/or damage by fire, lighting, explosion, earthquake, volcanism, tsunami, storm, cyclone, flood, inundation, landslide, theft, ground subsidence, existing underground cables and/or pipes or other underground facilities, bursting or overflowing of water tanks, apparatus or pipes, aircraft and other aerial devices or articles dropped therefrom, strike, riot and civil commotion, malicious damage, trespass, cessation of work whether total or partial, vibration and weakening of support. Unless otherwise insured by the Contractor, the CAR Insurance policy will exclude cover for construction plant, tools and equipment owned or hired by the Contractor or any sub-contractors. The Contractor shall keep such Works so insured notwithstanding any arrangement for Sectional Completion under Clause 21.0 or Partial Possession under Clause 16.0. The insurance policy shall be valid up to the Completion Date and the extended maintenance cover shall be for the Defect Liability Period plus a further three (3) Months. If the Contractor is unable to complete by the Completion Date or complete making good the Defects within the insured period, he shall ensure that the insurance is accordingly extended for the same period of delay. The Contractor shall effect the said extension of the insurance cover not less than one (1) Month before the expiry of the insurance currently in force. Where deductibles are specified in the Appendix or in the insurance policy, the Contractor shall bear the amount of all deductibles. The insurance policy shall also include the endorsement under Clause 19.1(a) to (d).',
					'priority'    => 78,
					'created_at'  => '2014-09-30 15:21:32.871248',
					'updated_at'  => '2014-09-30 15:21:32.871248',
				),
			79  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.A.2                   ',
					'description' => 'Additional risks to be covered under the insurance: Any additional risks or endorsements in addition to those stated in Clause 20.A.1 which may be required to be covered under the CAR Insurance policy shall be specified in the Contract Bills. If the Contractor having regard to his indemnity to the Employer under Clause 18.0, desires to have any additional endorsements to the insurance in addition to the risks specified, he shall do so at his own cost.',
					'priority'    => 79,
					'created_at'  => '2014-09-30 15:21:32.872153',
					'updated_at'  => '2014-09-30 15:21:32.872153',
				),
			80  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.A.3                   ',
					'description' => 'Placing of insurance with licensed insurance companies: The insurance referred to in Clause 20.A shall be placed with licensed insurance companies approved by the Employer, and the Contractor shall deposit with the Employer the policy and the receipt of premiums paid. If the Contractor makes default in insuring or continuing to insure as aforesaid, the Employer may insure against any risks in respect of which the default has occurred and the amount of premiums and any other cost incurred or paid by the Employer shall be set-off by the Employer under Clause 30.4.',
					'priority'    => 80,
					'created_at'  => '2014-09-30 15:21:32.872829',
					'updated_at'  => '2014-09-30 15:21:32.872829',
				),
			81  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.A.4                   ',
					'description' => 'Application of insurance claim proceeds: Upon the occurrence of any loss and/or damage to the Works or unfixed materials and goods prior to Practical Completion of the Works from any cause whatsoever, and notwithstanding that settlement of any insurance claim has not been completed, the Contractor shall with due diligence restore, replace or repair the same, remove and dispose of any debris and proceed with the carrying out and completion of the Works. All money if and when received from the insurance under this clause shall be paid in the first place to the Employer. The Employer shall retain the amount paid by the insurance companies in respect of professional fees for reinstatement and pay the balance to the Contractor and/or Nominated Sub-Contractors by installments under separate certificates to be issued by the Architect. The Contractor shall not be entitled to any additional payments in respect of the restoration of the damaged work and replacement or repair of any unfixed materials and goods and the removal and disposal of debris other than the monies received under the aforesaid insurance.',
					'priority'    => 81,
					'created_at'  => '2014-09-30 15:21:32.873645',
					'updated_at'  => '2014-09-30 15:21:32.873645',
				),
			82  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '23.3                     ',
					'description' => 'Insufficient information: If the Architect is of the opinion that the particulars submitted by the Contractor are insufficient to enable him to decide on the application for extension of time, the Architect shall within twenty eight (28) Days from receipt of the Contractor’s particulars under clause 23.1 (b), inform him of any deficiency in his submission and may required the Contractor to provide such further particulars within a further twenty eight (28) Days or within such period of times as may be stated by the Architect in writing.',
					'priority'    => 101,
					'created_at'  => '2014-09-30 15:21:32.888438',
					'updated_at'  => '2014-09-30 15:21:32.888438',
				),
			83  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.B.1                   ',
					'description' => 'Insurance by Employer: Without prejudice to the Contractor’s liability to indemnify the Employer under Clause 18.0, the Employer shall, as  a condition precedent to the commencement of any work under the Contract, take out and maintain in the joint names of the Employer, Contractor, sub-contractors and all interested parties a CAR Insurance policy for a value not less than the Contract Sum, plus the sum to cover professional fees for reinstatement and the sum to cover the removal of debris all as stated in the Appendix. Unless covered by the standard CAR Insurance policy, the insurance shall have endorsements to cover against loss and/or damage by fire, lighting, explosion, earthquake, volcanism, tsunami, storm, cyclone, flood, inundation, landslide, theft, ground subsidence, existing underground cables and/or pipes or other underground facilities, bursting or overflowing of water tanks, apparatus or pipes, aircraft and other aerial devices or articles dropped thereform, strike, riot and civil commotion, malicious damage, trespass, cessation of work whether total or partial, vibration and weakening of support. Unless separately required by Contractor at his own cost, the CAR Insurance policy will exclude cover for construction plant, tools and equipment owned or hired by the Contractor or any sub-contractors. The Employer shall keep such Works so insured notwithstanding any arrangement for Sectional Completion under Clause 21.0 or Partial Possession under Clause 16.0. The insurance policy shall be valid up to the Completion Date and the extended maintenance cover shall be for the Defect Liability Period plus a further three (3) Months. If the Contractor is unable to complete by the Completion Date or complete making good the Defects within the insured period, the Employer shall ensure that the insurance is accordingly extended for the same period of delay. The Employer shall effect the said extension of the insurance cover not less than one (1) Month before the expiry of the insurance currently in force. Where deductibles are specified in the Appendix or in the insurance policy, the Contractor shall bear the amount of all deductibles. The insurance policy shall also include the endorsement under Clause 19.1(a) to (d).',
					'priority'    => 82,
					'created_at'  => '2014-09-30 15:21:32.874441',
					'updated_at'  => '2014-09-30 15:21:32.874441',
				),
			84  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.B.2                   ',
					'description' => 'Additional risks required by the Contractor: Any additional risks or endorsements which vary from those stated in Clause 20.B.1 shall be specified in the Contract Bills, and the Employer shall ensure that the risks specified in the Contract Bills are covered by the CAR Insurance policy. If the Contractor having regard to his indemnity to the Employer under Clause 18.0, desires to have further additional endorsements to the insurance in addition to the risks specified, he shall do so at his own cost.',
					'priority'    => 83,
					'created_at'  => '2014-09-30 15:21:32.875415',
					'updated_at'  => '2014-09-30 15:21:32.875415',
				),
			85  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.B.3                   ',
					'description' => 'Maintenance of policy: The Employer shall maintain a proper insurance policy against the aforesaid risks and such policy and receipt for the last premium paid for its renewal shall, upon the request of the Contractor, be produced for his inspection.',
					'priority'    => 84,
					'created_at'  => '2014-09-30 15:21:32.876099',
					'updated_at'  => '2014-09-30 15:21:32.876099',
				),
			86  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.B.4                   ',
					'description' => 'Failure of Employer to insure: If the Employer at any time upon the request of the Contractor fails to produce any receipt showing such a policy as aforesaid to be effective, then the Contractor may take out and maintain in the joint names of the Employer, Contractor, sub-contractors and all interested parties, the CAR Insurance policy as required under Clauses 20.B.1 and 20.B.2. The Contractor upon production of the receipt of any premium paid by him shall be entitled to have the amount added to the Contract Sum.',
					'priority'    => 85,
					'created_at'  => '2014-09-30 15:21:32.87679',
					'updated_at'  => '2014-09-30 15:21:32.87679',
				),
			87  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.B.4                   ',
					'description' => 'Application of insurance claim proceeds: Upon the occurrence of any loss and/or damage to the Works or unfixed materials and goods prior to Practical Completion of the Works from any cause whatsoever notwithstanding that settlement of any insurance claim has not been completed, the Contractor shall with due diligence restore, replace or repair the same, remove and dispose of any debris and proceed with the carrying out and completion of the Works. All money if and when received from the insurance under this clause shall be paid in the first place to the Employer. The Employer shall retain the amount paid by the insurance companies in respect of professional fees for reinstatement and pay the balance to the Contractor and/or Nominated Sub-Contractors by installments under separate certificates issued by the Architect. The Contractor shall not be entitled to any additional payments in respect of the restoration of the damaged work and replacement or repair of any unfixed materials and goods and the removal and disposal of debris other than the monies received under the aforesaid insurance.',
					'priority'    => 86,
					'created_at'  => '2014-09-30 15:21:32.877467',
					'updated_at'  => '2014-09-30 15:21:32.877467',
				),
			88  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.C.1                   ',
					'description' => 'Employer’s risks – existing building or extension: Without prejudice to the Contractor’s liability to indemnify the Employer under Clause 18.0, the Employer shall, as  a condition precedent to the commencement of any work under the Contract, take out and maintain in the joint names of the Employer, Contractor, sub-contractors and all interested parties a CAR Insurance policy for a value not less than the Contract Sum, plus the value of the existing structure together with all the contents owned by the Employer or for which he is responsible, the sum to cover professional fees for reinstatement and the sum to cover the removal of debris all as stated in the Appendix. Unless covered by the standard CAR Insurance policy, the insurance shall have endorsements to cover against loss and/or damage by fire, lighting, explosion, earthquake, volcanism, tsunami, storm, cyclone, flood, inundation, landslide, theft, ground subsidence, existing underground cables and/or pipes or other underground facilities, bursting or overflowing of water tanks, apparatus or pipes, aircraft and other aerial devices or articles dropped thereform, strike, riot and civil commotion, malicious damage, trespass, cessation of work whether total or partial, vibration and weakening of support. Unless separately required by Contractor at his own cost, the CAR Insurance policy will exclude cover for construction plant, tools and equipment owned or hired by the Contractor or any sub-contractors. The Employer shall keep such Works so insured notwithstanding any arrangement for Sectional Completion under Clause 21.0 or Partial Possession under Clause 16.0. The insurance policy shall be valid up to the Completion Date and the extended maintenance cover shall be for the Defect Liability Period plus a further three (3) Months. If the Contractor is unable to complete by the Completion Date or complete making good the Defects within the insured period, the Employer shall ensure that the insurance is accordingly extended for the same period of delay. The Employer shall effect the said extension of the insurance cover not less than one (1) Month before the expiry of the insurance currently in force. Where deductibles are specified in the Appendix or in the insurance policy, the Contractor shall bear the amount of all deductibles. The insurance policy shall also include the endorsement under Clause 19.1(a) to (d).',
					'priority'    => 87,
					'created_at'  => '2014-09-30 15:21:32.878208',
					'updated_at'  => '2014-09-30 15:21:32.878208',
				),
			89  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.C.2                   ',
					'description' => 'Additional risks required by Contractor: Any additional risks or endorsements which vary from those stated in Clause 20.C.1 shall be specified in the Contract Bills, and the Employer shall ensure that the risks specified in the Contract Bills are covered by the CAR Insurance policy. If the Contractor having regard to his indemnity to the Employer under Clause 18.0, desires to have further additional endorsements to the insurance in addition to the risks specified, he shall do so at his own cost.',
					'priority'    => 88,
					'created_at'  => '2014-09-30 15:21:32.879125',
					'updated_at'  => '2014-09-30 15:21:32.879125',
				),
			90  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.C.3                   ',
					'description' => 'Maintenance of insurance by Employer: The Employer shall maintain a proper insurance policy against the aforesaid risks and such policy and receipt for the last premium paid for its renewal shall, upon the request of the Contractor, be produced for his inspection.',
					'priority'    => 89,
					'created_at'  => '2014-09-30 15:21:32.879821',
					'updated_at'  => '2014-09-30 15:21:32.879821',
				),
			91  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.C.4                   ',
					'description' => 'Failure of Employer to insure: If the Employer at any time upon the request of the Contractor fails to produce any receipt showing aforesaid to be effective, then the Contractor may take out and maintain in the joint names of the Employer, Contractor, sub-contractors and all interested parties, the CAR Insurance policy as required under Clauses 20.C.1 and 20.C.2. The Contractor upon production of the receipt of any premium paid by him shall be entitled to have the amount added to the Contract Sum.',
					'priority'    => 90,
					'created_at'  => '2014-09-30 15:21:32.880479',
					'updated_at'  => '2014-09-30 15:21:32.880479',
				),
			92  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '23.4                     ',
					'description' => 'Certificate of Extension of Time: When the Contractor has submitted sufficient particulars for the Architect’s consideration, the Architect shall subject to Clause 23.5, 23.6 and 23.8, consider the Contractor’s submission and shall either reject the Contractor’s application or issue a Certificate of Extension of Time within twenty six (26) Weeks from the receipt of sufficient particulars. The Architect may issue the written notice of rejection or the Certificate of Extension of Time before or after the Completion Date.',
					'priority'    => 102,
					'created_at'  => '2014-09-30 15:21:32.889137',
					'updated_at'  => '2014-09-30 15:21:32.889137',
				),
			93  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '20.C.5                   ',
					'description' => 'Application of insurance claim proceeds: Upon the occurrence of any loss and/or damage to the Works or unfixed materials and goods prior to Practical Completion of the Works from any cause whatsoever notwithstanding that settlement of any insurance claim has not been completed, the Contractor shall with due diligence restore, replace or repair the same, remove and dispose of any debris and proceed with the carrying out and completion of the Works including the reinstatement of the existing structure. All money if and when received from the insurance under this clause shall be paid in the first place to the Employer. The Employer shall retain the amount paid by the insurance companies in respect of professional fees for reinstatement and pay the balance to the Contractor and/or Nominated Sub-Contractors by installments under separate certificates issued by the Architect. The Contractor shall not be entitled to any additional payments in respect of the restoration of the damaged work and replacement or repair of any unfixed materials and goods and the removal and disposal of debris other than the monies received under the aforesaid insurance.',
					'priority'    => 91,
					'created_at'  => '2014-09-30 15:21:32.881163',
					'updated_at'  => '2014-09-30 15:21:32.881163',
				),
			94  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '21.1                     ',
					'description' => 'Commencement and Completion: On the Date of Commencement, possession of the Site shall be given to the Contractor who shall commence the execution of the Works and regularly and diligently proceed with and complete the same on or before the Completion Date. In the event there is a delay by the Employer in giving possession of the Site to the Contractor, the Architect shall grant an extension of time under Clause 23.8(f). Provided always that the delay in giving possession of the Site does not exceed the Period of Delay stated in the Appendix, the Contractor shall not be entitled to determine his own employment under the Contract.',
					'priority'    => 92,
					'created_at'  => '2014-09-30 15:21:32.881915',
					'updated_at'  => '2014-09-30 15:21:32.881915',
				),
			95  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '21.2                     ',
					'description' => 'Sectional Commencement Dates: Where there are different Dates of Commencement for sections of the Works, these shall be stated in the Appendix.',
					'priority'    => 93,
					'created_at'  => '2014-09-30 15:21:32.882652',
					'updated_at'  => '2014-09-30 15:21:32.882652',
				),
			96  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '21.3                     ',
					'description' => 'Sectional Completion Dates: Where there are different Completion Dates for sections of the Works stated in the Contract Documents, the Architect shall issue a Certificate of Sectional Completion when the sections of the Works are Practically Completed. The provisions in the Contract in regard to Practical Completion and the Defects Liability Period under Clause 15.0, extension of time under Clause 23.0, Liquidated Damages under Clause 22.0 and release of Retention Fund under Clause 30.6 shall apply with necessary changes as if each such section was a separate and distinct contract. ',
					'priority'    => 94,
					'created_at'  => '2014-09-30 15:21:32.883302',
					'updated_at'  => '2014-09-30 15:21:32.883302',
				),
			97  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '21.4                     ',
					'description' => 'Postponement or suspension of the Works: The Architect may issue an AI in regard to the postponement or suspension of all or any part of the Works to be executed under the Contract for a continuous period not exceeding the Period of Delay stated in the Appendix. If the insurance is covered by the Contractor Under Clauses 19.0 and 20.A, the Contractor shall ensure full insurance coverage for the whole period of postponement or suspension or if the insurance is covered by the Employer under Clause 20.B or 20.C, the Employer shall ensure similar insurance coverage.',
					'priority'    => 95,
					'created_at'  => '2014-09-30 15:21:32.88398',
					'updated_at'  => '2014-09-30 15:21:32.88398',
				),
			98  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '22.1                     ',
					'description' => 'Liquidated Damages and Certificate of Non-Completion: If the contractor fails to complete the Works by the completion Date, and the Architect is of the opinion that the same ought reasonably so to have been completed, the Architect shall issue a Certificate of Non-Completion. Upon the issuance of the Certificate of Non-Completion, the contractor shall pay or allow to the employer a sum calculated at the rate stated in the Appendix as Liquidated Damages for the period from the Completion Date to the date of Practical Completion. The Employer may recover such sum as a debt or may deduct such sum from any monies due or to become due to the Contractor under the Contract or the Employer may recover such sum from the Performance Bond. The Employer shall inform the contractor in writing of such deduction or such debt due from the Contractor. The imposition of Liquidated Damages by the Employer shall not be taken into account by the Architect in the issuance of payment certificates and Final Certificate, and is not subject to the set-off procedures under Clause 30.4 and adjudication.',
					'priority'    => 96,
					'created_at'  => '2014-09-30 15:21:32.884651',
					'updated_at'  => '2014-09-30 15:21:32.884651',
				),
			99  =>
				array(
					'clause_id'   => $main->id,
					'no'          => '22.2                     ',
					'description' => 'Agreed Liquidated Damages amount: The Liquidated Damages stated in the Appendix is a genuine pre-estimate of the loss and/or damage which the Employer will suffer in the event that the Contractor is in breach of Clauses 21.0 and 22.0. The parties agree that by entering into the Contract, the Contractor shall pay to the Employer the said amount, if the same becomes due without the need for the Employer to prove his loss and/or damage unless the contrary is proven by the Contractor.',
					'priority'    => 97,
					'created_at'  => '2014-09-30 15:21:32.885368',
					'updated_at'  => '2014-09-30 15:21:32.885368',
				),
			100 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '22.3                     ',
					'description' => 'Certificate of Non-Completion revoked by subsequent Certificate of Extension of Time: In the event the Architect issue a Certificate of Extension of Time under Clauses 23.4, 23.9 and 23.10 which has the effect of fixing a Completion Date which is later than the date stated in a Certificate of Non- Completion previously issued, such certificate shall have the effect of revoking the Certificate of Non-Completion earlier issued. The Employer shall then revise the amount of Liquidated Damages he is entitled to retain. In the event the amount of Liquidated Damages retained exceeds the amount the Employer is entitled to retain, he shall repay the surplus amount to the Contractor within the Period of Honouring Certificates from the date of the latest Certificate of Extension of Time. If the works is not completed by the Completion Date stated in such Certificate of Extension of Time, the Architect shall issue a further Certificate of Non-Completion. ',
					'priority'    => 98,
					'created_at'  => '2014-09-30 15:21:32.886146',
					'updated_at'  => '2014-09-30 15:21:32.886146',
				),
			101 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '23.1                     ',
					'description' => 'Submission of notice and particulars for extension of time: If the Contractor is of the opinion that the completion of the Works is or will be delayed beyond the Completion Date by any of the Relevant Events in Clause 23.8, he may apply for an extension of time provided always that: 23.1(a) the Contractor shall give written notice to the Architect his intention to claim for such extension of time together with an initial estimate of the extension of time he may require supported with all particulars of the cause of delay. Such notice must be given within twenty eight (28) Days from the date of the AI, CAI or the commencement of the Relevant Event, whichever is earlier. The giving of such written notice shall be a precedent to an entitlement of extension of time; and 23.1(b) within twenty eight (28) Days of the cause of delay, the Contractor shall send to the Architect his final claim for extension of time duly supported with all particulars to enable the architect to assess any extension of time to be granted. If the Contractor fails to submit such particulars within the stated time (or within such longer period as may be agreed in writing by the Architect), it shall be deemed that the Contractor has assessed that such Relevant Event will not delayed the completion of the Works beyond the Completion Date.',
					'priority'    => 99,
					'created_at'  => '2014-09-30 15:21:32.886981',
					'updated_at'  => '2014-09-30 15:21:32.886981',
				),
			102 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '23.2                     ',
					'description' => 'Delay by Nominated Sub-Contractor: Where the particulars of the written notice given under Clause 23.1 include references to Nominated Sub-Contractor, the Contractor shall forthwith send a copy of such written notice and particulars to the Nominated Sub-Contractor concerned.',
					'priority'    => 100,
					'created_at'  => '2014-09-30 15:21:32.887756',
					'updated_at'  => '2014-09-30 15:21:32.887756',
				),
			103 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '23.5                     ',
					'description' => 'Other consideration for extension of time: In assessing the extension of time, the Architect may take into account the following: 23.5(a) the effect or extent of any work omitted under the Contract, provided always that the Architect shall not fix a Completion Date that the Completion Date stated in the Appendix; and 23.5(b) any other Relevant Events which in the Architect’s opinion will have an effect on the Contractor’s entitlement to an extension of time.',
					'priority'    => 103,
					'created_at'  => '2014-09-30 15:21:32.88983',
					'updated_at'  => '2014-09-30 15:21:32.88983',
				),
			104 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '23.6                     ',
					'description' => 'Contractor to prevent delay: The Contractor shall constantly use his best endeavour to prevent or reduce delay in the progress of the Works, and to do all that may reasonably be required to the satisfaction of the Architect to prevent and reduce delay of further delay in the completion of the Works beyond the Completion Date.',
					'priority'    => 104,
					'created_at'  => '2014-09-30 15:21:32.890557',
					'updated_at'  => '2014-09-30 15:21:32.890557',
				),
			105 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '23.7                     ',
					'description' => 'Notification to Nominated Sub-Contractors: The Architect shall notify every Nominated Sub-Contractor in writing of each decision of the Architect when fixing a later Completion Date.',
					'priority'    => 105,
					'created_at'  => '2014-09-30 15:21:32.89122',
					'updated_at'  => '2014-09-30 15:21:32.89122',
				),
			106 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '23.8                     ',
					'description' => 'Relevant Events: The following are Relevant Events referred to in Clause 23.0: 23.8(a) Force Majeure; 23.8(b) exceptionally inclement weather; 23.8(c) loss and/or damage occasioned by one or more of the contingencies referred to in Clause 20.A, 20.B or 20.C as the case may be, provided always that the same is not due to any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors; 23.8(d) civil commotion, strike or lockout affecting any of the trades employed upon the Works or any of the trades engaged in the preparation, manufacture or transportation of any materials and goods required for the Works; 23.8(e) the Contractor not having received in due time the necessary AI (including those for or in regard to the expenditure of P.C. Sums and Provisional Sums, further drawings, details, levels and any other information) for which he had specifically applied in writing to the Architect in sufficient time before the commencement of construction of the affected works, to enable the Architect to issue the necessary AI within a period which would not materially affect the progress of the affected works, having regard to the Completion Date. Provided always that the AI was not required as a result of any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors; 23.8(f) delay by the Employer in giving possession of the Site or any section of the Site in accordance with Clause 21.1 and 21.2; 23.8(g) compliance with AI issued by Architect under Clauses 1.4, 11.2 and 21.4; 23.8(h) delay on the part of Nominated Sub-Contractors for the reasons set out in Clauses 21.4(a) to 21.4(w) of the PAM Sub-Contract 2006; 23.8(i) re-nomination of Nominated Sub-Contractors as set out in Clause 27.11; 23.8(j) delay on the part of craftsmen, tradesmen or other contractors employed or engaged by the Employer in executing work not forming part of the Contract or the failure to execute such work; 23.8(k) delay or failure in the supply of materials and goods which the Employer had agreed to supply for the Works; 23.8(l) the opening up for inspection of any work covered up, testing any materials, goods or executed work in accordance with Clause 6.3, unless the inspection or test: 23.8(l) (i) is provided for in the Contract Bills; 23.8(l) (ii) shows that the works, materials and goods were not in accordance with the contract; or 23.8(l) (iii) is required by the Architect in consequence of some prior negligence, omission, default and/or breach of contract by the Contractor; 23.8(m) any act of prevention or breach of contract by the Employer; 23.8(n) war damage under Clause 32.1; 23.8(o) compliance with AI issued in connection with the discovery of antiquities under Clause 33.1; 23.8(p) compliance with any changes to any law, regulations, by-law or terms and conditions of any Appropriate Authority and Service Provider; 23.8(q) delay caused by any Appropriate Authority and Service Provider in carrying out, or failure to carry out their work which affects the Contractor’s work progress, provided always that such delay is not due to any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors; 23.8(r) appointment of a replacement Person under Articles 3, 4, 5 and 6; 23.8(s) compliance with AI issued in connection with disputes with neighbouring property owners provided always that such dispute is not caused by negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors; 23.8(t) delay as a result of the execution of work for which a Provisional Quantity is included in the contract Bills which in the opinion of the Architect is not a reasonably accurate forecast of the quantity of work required; 23.8(u) failure of the Employer to give in due time entry to or exit from the Site or any part through or over any land, by way passage adjoining or connected to the Site and in possession or control of the Employer; 23.8(v) suspension by the Contractor of his obligation under Clauses 30.7 and 30.8; 23.8(w) suspension of the whole or part of the Works by order of an Appropriate Authority provided the same is not due to any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors; and 23.8(x) any other ground for extension of time expressly stated in the Contract.',
					'priority'    => 106,
					'created_at'  => '2014-09-30 15:21:32.891948',
					'updated_at'  => '2014-09-30 15:21:32.891948',
				),
			107 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '23.9                     ',
					'description' => 'Extension of time after the issuance of Certificate of Non-Completion: Where a Relevant Event occurs after the issuance of the Certificate of Non-Completion, the Architect shall grant an extension of time. The extension of time granted shall be added to the Completion Date of the Works or any section of the Works.',
					'priority'    => 107,
					'created_at'  => '2014-09-30 15:21:32.893595',
					'updated_at'  => '2014-09-30 15:21:32.893595',
				),
			108 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '23.10                    ',
					'description' => 'Architect’s review of extension of time after Practical Completion: The Architect may (but not obliged to) within twelve (12) Weeks after the date of Practical Completion review and fix a Completion Date later than that previously fixed, if in his opinion the fixing of such later Completion Date is fair and reasonable having regard to any of the Relevant Events, whether upon reviewing a previous decision or otherwise and whether or not a Relevant Event has been specifically notified by the Contractor under Clause 23.1. No such final review of extension of time shall result in a decrease in any extension of time already granted by the Architect. In the event the fixing of such later Completion Date affects the amount of Liquidated Damages the Employer is entitled to retain, he shall repay any surplus amount to the Contractor within the Period of Honouring Certificates.',
					'priority'    => 108,
					'created_at'  => '2014-09-30 15:21:32.894361',
					'updated_at'  => '2014-09-30 15:21:32.894361',
				),
			109 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '24.1                     ',
					'description' => 'Loss and/or expense caused by matters affecting the regular progress of the Works: Where the regular progress of the Works or any section of the Works has been or is likely to be materially affected by any of the matters expressly referred to in Clause 24.3, and the Contractor had incurred or is likely to incur loss and/or expense which could not be reimbursed by a payment made under any other provision in the Contract, the Contractor may make a claim for such loss and/or expense provided always that: 24.1(a) the Contractor shall give written notice to the Architect of his intention to claim such loss and/or expense together with an initial estimate of his claim duly supported with all necessary calculations. Such notice must be given within twenty eight (28) Days from the date of the AI, CAI or the start of the occurrence of the matters referred to in Clause 24.3, whichever is the earlier. The giving of such written notice shall be a condition precedent to any entitlement to loss and/or expense that the Contractor may have under the Contract and/or Common Law; and 24.1(b) within twenty eight (28) Days after the matters referred to in Clause 24.3 have ended, the Contractor shall send to the Architect and Quantity Surveyor, complete particulars of his claim for loss and/or expense together with all necessary calculations to substantiate his claims. If the Contractor fails to submit the required particulars within the stated time (or within such longer period as may be agreed in writing by the Architect), it shall be deemed that the Contractor had waives his rights for loss and/or expense.',
					'priority'    => 109,
					'created_at'  => '2014-09-30 15:21:32.895152',
					'updated_at'  => '2014-09-30 15:21:32.895152',
				),
			110 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '24.2                     ',
					'description' => 'Access to Contractor’s books and documents: The Contractor shall keep contemporaneous records of all his claims for loss and/or expense and shall submit all particulars to the Architect. The Architect and Quantity Surveyor shall have access to all books, documents, reports, papers or records in the possession, custody or control of the Contractor that are material to the claim and the Contractor shall provide free of charge, a copy each to the Architect and Quantity Surveyor when requested. All such documents shall remain available in accordance with this clause until all claims have been resolved. The Contractor shall use his best endeavour to ensure that all such documents in the possession, custody or control of sub-contractors and/or suppliers that are material to the claim are similarly available.',
					'priority'    => 110,
					'created_at'  => '2014-09-30 15:21:32.895975',
					'updated_at'  => '2014-09-30 15:21:32.895975',
				),
			111 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '24.3                     ',
					'description' => 'Matters materially affecting the regular progress of the Works: The following are the matters referred to in Clause 24.1: 24.3(a) the Contractor not having received in due time the necessary AI (including those for or in regard to the expenditure of P.C Sums and Provisional Sums, further drawings, details, levels and any other information) for which he had specifically applied in writing to the Architect. The Contractor’s application must be submitted to the Architect in sufficient time before the commencement of construction of the affected works, enable the Architect to issue the necessary AI within a period which would not materially affect the progress of the affected works, having regard to the Completion Date. Provided always that the AI was not required as a result of any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors; 24.3(b) delay by the Employer in giving possession of the site or any section of the Site in accordance with Clauses 21.1 and 21.2; 24.3(c) compliance with a written instruction issued by the Architect in regard to the postponement or suspension of all or any part of the Works to be executed under Clause 21.4; 24.3(d) delay on the part of craftsmen, tradesmen or other contractors employed or engaged by the Employer in executing work not forming part of the Contract or the failure to execute such work; 24.3(e) delay or failure in the supply of materials and goods which the Employer had agreed to supply for the Works 24.3(f) the opening up for inspection of any work covered up, testing any materials and good or executed work in accordance with Clause 6.3, unless the inspection or test showed that the works, materials and goods were not in accordance with the Contract or was in the opinion of the Architect required in consequence of some prior negligence, omission, default and/or breach of contract by the Contractor; 24.3(g) any act of prevention or breach of contract by the Employer; 24.3(h) delay as a result of a compliance with AI issued in connection with the discovery of antiquities under Clause 33.1; 24.3(i) appointment of a replacement Person under Articles 3, 4, 5 and 6; 24.3(j) compliance with a written instruction issued by the Architect in connection with disputes with neighbouring property owners provided always that same is not caused by negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractor; 24.3(k) by reason of the execution of work for which a Provisional Quantity is included in the Contract Bills which in the opinion of the Architect is not a reasonably accurate forecast of the quantity of work required; 24.3(l) failure of the Employer to give in due time entry to or exit from the Site or any part through or over any land, by way of passage adjoining or connected to the Site and in the possession or control of the Employer; 24.3(m) suspension by the Contractor of his obligations under Clauses 30.7 and 30.8; and 24.3(n) suspension of the whole part of the Works by order of an Appropriate Authority provided always that the same is due to negligence or omission on the part of the Employer, Architect or Consultant.',
					'priority'    => 111,
					'created_at'  => '2014-09-30 15:21:32.896738',
					'updated_at'  => '2014-09-30 15:21:32.896738',
				),
			112 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '24.4                     ',
					'description' => 'Loss and/or expense to be included in certificate: Subject to the Contractor complying with Clause 24.1, the Architect or Quantity Surveyor shall ascertain the amount of such loss and/or expense. Any amount so ascertained from time to time for such loss and/or expense shall be added to the Contract Sum, and if an Interim Certificate is issued after the date of ascertainment, such amount shall be included in the certificate.',
					'priority'    => 112,
					'created_at'  => '2014-09-30 15:21:32.897754',
					'updated_at'  => '2014-09-30 15:21:32.897754',
				),
			113 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '31.4                     ',
					'description' => 'Payment resulting from determination: Upon the expiration of fourteen (14) Days from the date on which written notice of determination has been given by either party under Clause 31.1m or where on completion of the works required by the Architect under Clause 31.3, or abandonment as the case may be of any such work, the provisions of Clause 263.4 shall apply.',
					'priority'    => 171,
					'created_at'  => '2014-09-30 15:21:32.942167',
					'updated_at'  => '2014-09-30 15:21:32.942167',
				),
			114 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '25.1                     ',
					'description' => 'Default by Contractor: The Employer may determine the employment of the contractor if the Contractor defaults in any of the following: 25.1(a) if without reasonable cause, he fails to commence the works in accordance with the contract; 25.1(b) if without reasonable cause, he wholly or substantially suspends the carrying out of the Works before completion; 25.1(c) if he fails to proceed regularly and/or diligently with the Works; 25.1(d) if he persistently refuses or neglects to comply with an AI; 25.1(e) if he fails to proceed regularly or neglects to comply with an AI; 25.1(f) if he has abandoned the Works.',
					'priority'    => 113,
					'created_at'  => '2014-09-30 15:21:32.898439',
					'updated_at'  => '2014-09-30 15:21:32.898439',
				),
			115 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '25.2                     ',
					'description' => 'Procedure for determination: Upon the occurrence of any default under Clause 25.1, and if the Employer decides to determine the contractor’s employment, the Employer or Architect on his behalf shall give to the Contractor a written notice delivered by hand or by registered post specifying the default. If the contractor shall continue with such default for fourteen (14) Days from the receipt of such written notice, then the Employer may, within ten (10) Days from the expiry of the said fourteen (14) Days, by a further written notice delivered by hand or by registered post, forthwith determine the employment of the contractor under the Contract. Provided always that such notice shall not be given unreasonably or vexatiously.',
					'priority'    => 114,
					'created_at'  => '2014-09-30 15:21:32.899195',
					'updated_at'  => '2014-09-30 15:21:32.899195',
				),
			116 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '25.3                     ',
					'description' => 'Contractor’s insolvency: In the event of the Contractor becoming insolvent or making a composition or arrangement with his creditors, or have a winding up order made, or (except for purposes of reconstruction or amalgamation) a resolution for voluntary winding up, or having a liquidator or receiver or manager of his business or undertaking duly appointed, or having possession taken by or on behalf of the holders of any debentured secured by a floating charge, or of any property comprised in or subject to the floating charge, the employment of the Contractor shall be forthwith automatically determined.',
					'priority'    => 115,
					'created_at'  => '2014-09-30 15:21:32.899936',
					'updated_at'  => '2014-09-30 15:21:32.899936',
				),
			117 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '25.4                     ',
					'description' => 'Rights and duties of Employer and Contractor: In the event that the employment of the Contractor is determined under Clauses 25.1 or 25.3, the following shall be respective rights and duties of the Employer and Contractor: 25.4(a) the contractor shall vacate the Site and return possession of the Site to the Employer who may employ and pay other person to carry out and complete the Works and to make good any defects. Such Person may enter upon the Works and use all temporary building, construction plant, tools, materials and good intended for, delivered to and placed on or adjacent to the Site (except those construction plant that is on hire by the Contractor) and may purchase all materials and goods necessary for carrying out and the completion of the Works. The Contractor if so required by the Employer or by the Architect on behalf of the Employer shall within twenty one (21) Days of the date of determination, assign to the Employer the benefit of any agreement for the continuation of the hire construction plant and equipment already in the Site: 25.4(b) the Contractor if so required by the Employer or Architect, shall within twenty one (21) Days of the date of determination, assign to the Employer without payment the benefit of any agreement for the supply of materials, goods and/or for the execution of any work for the purposes of the Contract to the extent that the same is assignable; 25.4(c) the Contractor when instructed in writing by the Architect shall remove from the Woks any temporary building, construction plant, tools equipment, materials and goods belonging to or hired by him. If within a reasonable time after any such instruction has been issued to the Contractor, and he has not complied therewith, then the employer may without liability remove and sell any such property belonging to the Contractor except those that are on hire and hold the proceeds less all costs incurred to the credit of the Contractor; and 25.4(d) the Contractor shall allow or pay the Employer all cost incurred to complete the Works including all loss and/or expenses suffered by the Employer. Until after the completion of the Works under Clauses 25.4(a), the Employer shall not be bound by any provision in the Contract to make any further payment to the Contractor, including payment which have been certified but not yet paid when the employment of the Contractor was determined. Upon completion of the Works, am account taking into consideration that value of works carried out by the Contractor and all cost incurred by the Employer to complete the Works including loss and/or expense suffered by the Employer shall be incorporated in a final account prepared in accordance with clauses 25.6.',
					'priority'    => 116,
					'created_at'  => '2014-09-30 15:21:32.900661',
					'updated_at'  => '2014-09-30 15:21:32.900661',
				),
			118 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '25.5                     ',
					'description' => 'Records of Works: The Architect or Quantity Surveyor shall within twenty eight (28) Days of the determination of the Contractor’s employment, give a written notice to the contractor of the date of inspection on Site to jointly record the extent of the Works executed and the materials and goods delivered to the Site. The Contractor shall provide all necessary assistance to the Architect and Quantity Surveyor to perform their task. Upon completion of the record by the Architect or Quantity Surveyor, a copy shall be sent to the Contractor and such records shall form the basis for the evaluation of the value of the works executed and materials and goods delivered to the Site by the Contractor up to the date of determination.',
					'priority'    => 117,
					'created_at'  => '2014-09-30 15:21:32.901585',
					'updated_at'  => '2014-09-30 15:21:32.901585',
				),
			119 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '25.6                     ',
					'description' => 'Final Account upon determination: The Architect or Quantity Surveyor shall within six (6) Months on completion of the Works, submit to the Employer and Contractor for their agreement, a final account for all const incurred to complete the Works including the sums previously certified to the Contractor before the date of determination, Liquidated Damages, set-off and all other loss and/or expense suffered. 25.6(a) if nothing in the said final account is disputed by the Employer or Contractor within three (3) months from the date of receipt of the final account from the Architect or Quantity Surveyor, the final account shall be conclusive and deemed agreed by the parties. If the amount in the final account exceeds the total amount which would have been payable on completion in accordance with the Contract, the difference shall be a debt payable to the Employer by the Contractor or where applicable, the Employer may recover such sum from the Performance Bond. If the said amount is less that the said total amount, the difference shall be a debt payable to the Contractor by the Employer. 25.6(b) if either party has any dispute on the final account, the party disputing the final account shall by written notice to the other party (with copies to the Architect and Quantity surveyor) set out any disagreement complete with particulars within three (3) Months of the date of receipt of the final account from the Architect or Quantity Surveyor. The Architect or Quantity Surveyor within three (3) Months from the date of receipt of the grounds of dispute shall either amend or not amend the final account. Any party disagreeing with the amended final account or decision not to amend the final account shall refer the dispute to arbitration under Clauses 34.0 within three (3) Months from the date of receipt of the amended final account or decision not to amend the final account. Failure to refer the dispute to arbitration within the stipulated time, the final account or amended final account shall deem to be conclusive and agreed by the parties. 25.6(c) Any dispute on Liquidated Damages, set-off and invest which the Employer is entitle to make under the Contract shall be referred to arbitration.',
					'priority'    => 118,
					'created_at'  => '2014-09-30 15:21:32.902297',
					'updated_at'  => '2014-09-30 15:21:32.902297',
				),
			120 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '25.7                     ',
					'description' => 'Remedy limited to damages only: Upon receipt of a written notice by the contractor from the Employer to determine the employment of the Contractor, the Contractor shall yield possession of the Site within fourteen (14) Days from the receipt of the said written notice and shall removes his personnel and labour force (but not construction plant, tools and equipment unless so instructed by the Architect) from the Site. Irrespective of the validity if the said written notice the Contractor’s remedy shall be limited to compensation for damages only',
					'priority'    => 119,
					'created_at'  => '2014-09-30 15:21:32.903247',
					'updated_at'  => '2014-09-30 15:21:32.903247',
				),
			121 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '25.8                     ',
					'description' => 'Employer’s rights and remedies not prejudiced: The provisions of Clause 25.0 are without prejudice to any other rights and/or remedies which the Employer may possess.',
					'priority'    => 120,
					'created_at'  => '2014-09-30 15:21:32.904033',
					'updated_at'  => '2014-09-30 15:21:32.904033',
				),
			122 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '26.1                     ',
					'description' => 'Defaults by Employer: The Contractor may determine his own employment if the Employer defaults in any of the following: 26.1(a) if the Employer fails or neglects to pay the Contractor the amount due on any certificate (less any Liquidated Damages and set-off which the Employer is expressly entitled to make under the Contract) within the Period of Honouring Certificates; 26.1(b) if the Employer interferes with or obstructs the issue of any certificate by the Architect; 26.1(c) if the Employer fails to nominate a succeeding Architect or Consultant in accordance with Articles 3, 4, 5 and 6; or 26.1(d) if before the date of Practical Completion, the carrying out of the whole or substantially the whole of the uncompleted Works is suspended for a continuous period of time exceeding that stated in the Period of Delay stated in the Appendix by reason of: 26.1(d) (i) AI issued by the Architect under Clause 1.4, 21.1 or 21.4 unless the instruction is issued to rectify any negligence, omission, default and/or breach of contract by the Contractor or Nominated Sub-Contractor; 26.1(d) (ii) the Contractor not having received in due time the necessary AI (including those for or in regard to the expenditure of P.C. Sums and Provisional Sums, further drawing, details, levels and any other information) for which he had specifically applied in writing to the Architect. The Contractor’s application must be submitted to the Architect in sufficient time before the commencement of construction of the affected works, to enable the Architect to issue the necessary AI within a period which would not materially affect the progress of the affected works, having regard to the Completion Date. Provided always that the AI was not required as a result of any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors; 26.1(d) (iii) delay on the part of craftsmen, tradesmen or other contractors employed or engaged by the Employer in executing work not forming part of the Contractor or the failure to execute such work; or 26.1(d) (iv) the opening up for inspection of any work covered up or to arrange for a carry out testing of any work, materials and goods in accordance with Clause 6.3 unless the inspection or test showed that the work, materials and goods were not in accordance with the Contract, or the inspection and/or test was in the opinion of the Architect required in consequence of some prior negligence, omission, default and/or breach of contract by the Contractor.',
					'priority'    => 121,
					'created_at'  => '2014-09-30 15:21:32.904775',
					'updated_at'  => '2014-09-30 15:21:32.904775',
				),
			123 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '26.2                     ',
					'description' => 'Procedures for determination: Upon the occurrence of any default under Clause 26.1, and if the Contractor decides to determine his own employment then, the Contractor shall give to the Employer a written notice delivered by hand or by registered post specifying the default. If the Employer shall continue with such default for fourteen (14) Days from the receipt of such written notice then, the Contractor may within ten (10) Days from the expiry of the said fourteen (14) Days, by a further written notice delivered by hand or by registered post forthwith determine his own employment under the Contract. Provided always that such notice shall not be given unreasonably or vexatiously.',
					'priority'    => 122,
					'created_at'  => '2014-09-30 15:21:32.905758',
					'updated_at'  => '2014-09-30 15:21:32.905758',
				),
			124 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '26.3                     ',
					'description' => 'Employer’s insolvency: In the event of the Employer becoming insolvent or making a composition or arrangement with  his creditors, or have a winding up order made, or (except for the purpose of reconstruction or amalgamation) a resolution for voluntary winding up, or having a liquidator or receiver or manager of his business or undertaking duly appointed, or having possession taken by or on behalf of the holders of any debentures secured by a floating charge, or of any property comprised in or subject to the floating charge, the employment of the Contractor shall be forthwith automatically determined.',
					'priority'    => 123,
					'created_at'  => '2014-09-30 15:21:32.90651',
					'updated_at'  => '2014-09-30 15:21:32.90651',
				),
			125 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '26.4                     ',
					'description' => 'Rights and duties of Contractor and Employer: In the event that the employment of the Contractor is determined under Clause 26.1 or 26.3, the following shall be respective rights and duties of the Contractor and Employer: 26.4(a) the Contractor shall within fourteen (14) Days or within such period as may be agreed in writing by the Architect, remove from the Site all his temporary buildings, construction plant, tools, materials and goods and shall give facilities for his Nominated Sub-Contractors to do the same; and 26.4(b) the Employer shall allow or pay to the Contractor the total value of work properly executed and the value of materials and goods supplied including any loss and/or expense suffered by the Contractor caused by such determination.',
					'priority'    => 124,
					'created_at'  => '2014-09-30 15:21:32.907246',
					'updated_at'  => '2014-09-30 15:21:32.907246',
				),
			126 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '26.5                     ',
					'description' => 'Records of Works: The Contractor shall within twenty eight (28) Days of the determination of his own employment, give a written notice to the Architect and Quantity Surveyor of the date of inspection on Site to jointly record the extent of the Works executed and the materials and goods delivered to the Site. Upon completion of the record by the Contractor, a copy shall be sent to the Employer, Architect and Quantity Surveyor and such records shall form the basis of the evaluation of the value of the work executed and materials and goods delivered to the Site by the Contractor up to the date of determination.',
					'priority'    => 125,
					'created_at'  => '2014-09-30 15:21:32.907967',
					'updated_at'  => '2014-09-30 15:21:32.907967',
				),
			127 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '26.6                     ',
					'description' => 'Settlement of accounts: The Contractor shall within six (6) Months after determination of his own employment, submit to the Employer, Architect and Quantity Surveyor for the Employer’s agreement, a final account for the total value of work properly executed, the value of materials and goods supplied and loss and/or expense suffered by the Contractor caused by such determination. 26.6(a) if nothing in the said final account is disputed by the Employer within three (3) Months from the date of receipt of the final account from the Contractor, the final account shall be conclusive and deemed agreed by the parties. If the amount in the final account exceeds the sum previously paid to the Contractor under the Contract (less any Liquidated Damages and set-off which the Employer is expressly entitled under the Contract), the balance shall be debt payable to the Contractor by the Employer within the Period of Honouring Certificates. If the said amount is less than the said sum, the difference shall be a debt payable to the Employer by the Contractor or where applicable, the Employer may recover such difference from the Performance Bond. 26.6(b) If the Employer disputes the final account, the Employer shall given written notice to the Contractor setting out any disagreement complete with particulars within three (3) Months of the date of receipt of the final account from the Contractor. The Contractor shall within three (3) Months from the date of receipt of the grounds of dispute, either make such amendment to the final account as in his opinion may be appropriate, or decide not to amend the final account. In the event the Employer disagrees with the amended final account or the decision not to amend the final account, the Employer shall refer the dispute to arbitration within the stipulated time, the final account of amended final account shall deem to be conclusive and agreed by the parties.',
					'priority'    => 126,
					'created_at'  => '2014-09-30 15:21:32.908685',
					'updated_at'  => '2014-09-30 15:21:32.908685',
				),
			128 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '26.7                     ',
					'description' => 'Contractor’s rights and remedies not prejudiced: The provisions of Clause 26.0 are without prejudice to any other rights and/or remedies which the Contractor may possess.',
					'priority'    => 127,
					'created_at'  => '2014-09-30 15:21:32.909471',
					'updated_at'  => '2014-09-30 15:21:32.909471',
				),
			129 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.1                     ',
					'description' => 'P.C. Sums and Provisional Sums – Nominated Sub-Contractors: The following provisional shall apply where P.C Sums are include in the Contract Bills or arise as a result of an AI given in regard to the expenditure of Provisional Sums in respect of a Person to be nominated by the Architect to supply and fix material and goods or to instruct, and such Person who is nominated by the Architect is hereby referred to as “Nominated Sub-Contractor” employed by the Contractor. If the Nominated Sub-Contractor proposes any alternative design to the sub-contract drawings or if the sub-contract leaves any matter of design, specification or choice of materials, goods and workmanship to the Nominated Sub-Contractor, the Nominated Sub-Contractor and not the Contractor shall be responsible to ensure that such sub-contract works are fit for its purpose.',
					'priority'    => 128,
					'created_at'  => '2014-09-30 15:21:32.910136',
					'updated_at'  => '2014-09-30 15:21:32.910136',
				),
			130 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.2                     ',
					'description' => 'Nomination of sub-contractor : The Architect shall not nominate any Person as Nominated Sun-Contractor against whom the Contractor makes reasonable objection in accordance with Clause 27.3. the Contractor shall make such reasonable objection in writing not later than fourteen (14) Days from receipt of the nomination instruction from the Architect. The Architect  shall not  nominate (except where Architect and Contractor otherwise agree) any Person who will not enter into a contract with the Contractor based upon the term and condition of the PAM Sub-Contract 2006 which provides iner alia: 27.2(a) that the Nominated Sub-Contractor carry out and complete the sub-contract works in every respect to reasonable satisfaction of the Contractor and Architect and in conformity with all reasonable direction and requirement of the Contractor; 27.2(b) that the Nominated Sub-Contractor observe, perform and comply with all the provisions of the Contract which the Contractor is obliged to perform and comply with so far as they relate and apply to the sub-contract works. 27.2(c) that the Nominated Sub-Contractor indemnify the Contractor against the same liabilities in respect of the sub-contract works as those for which the Contractor is liable to indemnity the Employer under the Contract; 27.2(d) that the Nominated Sub-Contractor indemnify the Contractor against claims in respect of any negligence, omission or default of his sub-contractors, his servants or agents or any misuse by him or them of any construction plant, access, scaffolding, temporary works, appliances or other property belonging to or provided by the Contractor; 27.2(e) that the sub-contractor works be completed within the periods specified and the Contractor shall not without the written recommendation of the Architect grant any extension of time for the completion of sub-contract works caused by any of the Relevant Event stated in Clause 21.4 of the PAM Sub-Contract 2006. Where the delays are caused by negligence, omission, default and/or breach of the sub-contract by the Contractor, the Contractor is solely responsible under Clause 21.6 of the PAM Sub-Contract 2006 to access and grant an extension of time to the Nominated Sub-Contractor; 27.2(f) that when the Contractor and Nominated Sub-Contractor consider that the sub-contract works have been practically completed, they shall request the Architect issue a certificate to the effect, and if the Architect is of the opinion of Clause 7.1 of the PAM Sub-Contract 2006, the Architect shall forthwith issue a certificate to the effect; 27.2(g) that if the Nominated Sub-Contractor fails to complete the sub-contract works within the sub-contract completion date or within any extension time granted by the Contractor, and the Contractor after having given a written notification to the Nominated Sub-Contractor after having given a written notification to the Nominated Sub-Contractor that sub-contract works ought reasonably so to have been completed. The Nominated Sub-Contractor shall pay or allow to the Contractor loss and/or expense suffered by the Contractor or an agreed Liquidated Damages; 27.2(h) that payment to Nominated Sub-Contractor shall be made within seven ( 7 ) Days after the Period of Honouring Certificates and shall be subject to the retention and deduction expressly provided under the PAM Sub-Contract  2006; and 27.2(i) that the Architect, Consultants and their authorized representative shall have the right of access to the workshops and other places of the Nominated Sub-Contractor in accordance with provision of Clause 11.2 the PAM Sub-Contract 2006',
					'priority'    => 129,
					'created_at'  => '2014-09-30 15:21:32.910878',
					'updated_at'  => '2014-09-30 15:21:32.910878',
				),
			131 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.3                     ',
					'description' => 'Objection to nomination of sub-contractor: Subject to Clause 27.4, the Contractor shall not be required to enter into a sub-contract with any Nominated Sub-Contractor against whom the Contractor has made a reasonable objection based on available known facts and documented evidence that the financial standing or solvency or technical competence of the Nominated Sub-Contractor is such that a prudent contractor, having regard to the scope of the sub-contract works would be justified in rejecting the nominating.',
					'priority'    => 130,
					'created_at'  => '2014-09-30 15:21:32.911971',
					'updated_at'  => '2014-09-30 15:21:32.911971',
				),
			132 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.4                     ',
					'description' => 'Action following objection of Nominated Sub-Contractor: Where the Architect is of the opinion that the Contractor has made a reasonable objection, the Architect may either issue further written instruction to remove the objection to that the Contractor can enter into the sub-contract, or cancel such nomination instruction and issue an instruction omitting the work which was the subject of the nomination instruction or re-nominate another sub-contractor for the sub-contract works.',
					'priority'    => 131,
					'created_at'  => '2014-09-30 15:21:32.912711',
					'updated_at'  => '2014-09-30 15:21:32.912711',
				),
			133 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.5                     ',
					'description' => 'Payment by Contractor to Nominated Sub-Contractors: The Architect shall direct the Contractor as to the total value of work properly executed and include the percentage of the values of materials and goods state in the Appendix in the calculation of the amount stated to be due in any certificate issued under Clause 30.0, and shall at the same time when the certificate is issued, inform the Nominated Sub-Contractor in writing of the amount of the said total value. The sum representing such total value (less any retention and deduction expressly provided under PAM Sub-Contract 2006) shall be paid by Contractor to the Nominated Sub-Contractor within seven (7) Days after the Period of Honouring Certificates.',
					'priority'    => 132,
					'created_at'  => '2014-09-30 15:21:32.913389',
					'updated_at'  => '2014-09-30 15:21:32.913389',
				),
			134 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.6                     ',
					'description' => 'Failure of Contractor to pay Nominated Sub-Contractors: The Architect may at any time before the issuance of any Interim and Penultimate Certificate, requests the Contractor to furnish to him reasonable poor that all amounts stated as due and included in the previous certificates have been discharged. The Contractor shall provide such proof within fourteen (14) Days of the Architect request. If the Contractor has any reasons for withholding any Nominated Sub-Contractor’s payment under Clauses 16.1 and 26.13 of the PAM Sub-Contract 2006, he shall provide the Architect written details as his compliance. If the Contractor fails to comply with the Architect‘s request within fourteen (14) Days, the Architect any (but not obliged to) issue a certificate stating the amount in respect which the Contractor has fails to provide such proof. Where the Architect has so certified, the Employer may (but not obliged to) pay such amounts directly to the Nominated Sub-Contractor and deduct the same from any sum de or to become due to the Contractor. The Architect may issue the aforesaid certificate irrespective of whether or not an Interim Certificate under Clause 30.0 is due for issuance.',
					'priority'    => 133,
					'created_at'  => '2014-09-30 15:21:32.914096',
					'updated_at'  => '2014-09-30 15:21:32.914096',
				),
			135 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.7                     ',
					'description' => 'Final payment to Nominated Sub-Contractors: If the Architect wishes to make final payment to any Nominated Sub-Contractor before final payment is due to the Contractor, and if the Nominated Sub-Contractor has indemnified the Contractor against all of his liabilities under the Nominated Sub-Contract, the Architect shall issue a certificate to the Contractor and the Contractor shall pay to such Nominated Sub-Contractor the amount so certified less any retention and deductions expressly provided under PAM Sub-Contract 2006. Upon such final payment, the amount stated in the Appendix as Limit of Retention Fund shall be reduced by the sum of the retention released to the Nominated Sub-Contractor.',
					'priority'    => 134,
					'created_at'  => '2014-09-30 15:21:32.914853',
					'updated_at'  => '2014-09-30 15:21:32.914853',
				),
			136 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.8                     ',
					'description' => 'Determination of the Nominated Sub-Contractor’s employment: The Contractor shall not determine the employment of any Nominated Sub-Contractor without the written consent of the Architect. If the Contractor intends to determine the employment of the Nominated Sub-Contractor, the Contractor shall send to Architect a written report stating the Nominated Sub-Contractor’s default with a copy to the Nominated Sub-Contractor. The Architect may request that the Nominated Sub-Contractor respond to the Contractor’s report before he decides whether pr not to give his written consent.',
					'priority'    => 135,
					'created_at'  => '2014-09-30 15:21:32.91554',
					'updated_at'  => '2014-09-30 15:21:32.91554',
				),
			137 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.9                     ',
					'description' => 'Contractor’s responsibility for Nominated Sub-Contractors: The Contractor  shall be fully responsible to ensure that all Nominated Sub-Contractors carry put the sub-contract works in accordance with the Nominated Sub-Contract and in compliance therewith provide designs (if any), materials, goods and standards of workmanship of the quality and standard specified therein to the reasonable satisfaction of the Architect.',
					'priority'    => 136,
					'created_at'  => '2014-09-30 15:21:32.916232',
					'updated_at'  => '2014-09-30 15:21:32.916232',
				),
			138 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.10                    ',
					'description' => 'Employer no privity of Contract with Nominated Sub-Contractors: Neither the existence of or exercise of the foregoing provisions nor anything else contained in the Contract shall create a privity of contract between the Employer and any of the Nominated Sub-Contractor.',
					'priority'    => 137,
					'created_at'  => '2014-09-30 15:21:32.91695',
					'updated_at'  => '2014-09-30 15:21:32.91695',
				),
			139 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.11                    ',
					'description' => 'Re-nomination of sub-contractor due to determination by the Contractor: If the employment of a Nominated Sub-Contractor is determined by the Contractor with written consent of the Architect shall re-nominate another Nominated Sub-Contractor. In the event, the Contractor shall be entitled to be paid such difference (if any) between the sum payable to the Contractor and new Nominated Sub-Contractor and the any sum that will be recoverable from the defaulting Nominated Sub-Contractor under Clause 27.13. An extension of time under Clause 23.8(i) may be granted to the Contractor but the Contractor shall not be entitled to any damages, loss and/or expense.',
					'priority'    => 138,
					'created_at'  => '2014-09-30 15:21:32.917627',
					'updated_at'  => '2014-09-30 15:21:32.917627',
				),
			140 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.12                    ',
					'description' => 'Re-nomination of sub-contractor due to determination by the Nominated Sub-Contractor: If a Nominated Sub-Contractor determines his own employment under the Nominated Sub-Contract due to negligence, omission, default or breach of the Contractor, the Architect shall re-nominate another Nominated Sub-Contractor. In the event, the Contractor shall be paid the same sum as would been payable to previous Nominated Sub-Contractor. The Contractor will be liable to pay the new Nominated Sub-Contractor any additional cost to complete the Sun-Contract Works and to pay the Employer far additional cost incurred in re-nomination and loss and/or expense suffered by the Employer by such determination. The Contractor shall not be entitled to any extension of time unless and until the Contractor has established that the determination by the Nominated Sub-Contractor of his own employment is invalid. In the event the determination by the Nominated Sub-Contractor of his own employment has been established to be invalid by arbitration or litigation, Clause 27.11 will apply.',
					'priority'    => 139,
					'created_at'  => '2014-09-30 15:21:32.918372',
					'updated_at'  => '2014-09-30 15:21:32.918372',
				),
			141 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.13                    ',
					'description' => 'Contractor to recover additional expenses from Nominated Sub-Contractor: In the event the Architect consents to determine the employment of the Nominated Sub-Contractor under clause 27.11, the Contractor shall recover all additional expenses (including any additional expenses incurred by the Employer) from the Nominated Sub-Contractor as debt or from any monies due or to become due to the Nominated Sub-Contractor and failing which, the Contractor may recover such sum from the Nominated Sub-Contractor’s Performance Bond.',
					'priority'    => 140,
					'created_at'  => '2014-09-30 15:21:32.91919',
					'updated_at'  => '2014-09-30 15:21:32.91919',
				),
			142 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '27.14                    ',
					'description' => 'Contractor permitted to tender for P.C. Sums: Where the Contractor carries out works for which P.C. Sums and Provisional Sum are included in the Contract Bills, the Contractor shall be permitted to tender for the same. If the tender of the Contractor for such work is acceptable, it shall be considered as a Variation and the Contractor shall not be entitled to profit and attendance charges as priced under the relevant P.C. Sum, notwithstanding the provision of Clause 30.11(c)',
					'priority'    => 141,
					'created_at'  => '2014-09-30 15:21:32.919892',
					'updated_at'  => '2014-09-30 15:21:32.919892',
				),
			143 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '28.1                     ',
					'description' => 'P.C. Sums and Provisional Sums – Nominated Suppliers: The following provisions of this clause shall apply where P.C. Sum are included in the Contract Bills or arise as a result of on AL given in regard to the expenditure of Provisional Sums in respect of Person to be nominated by the Architect to supply materials and goods to be fixed by the Contractor. Such Person as the Architect shall instruct is referred to as “Nominated Supplier”.',
					'priority'    => 142,
					'created_at'  => '2014-09-30 15:21:32.920618',
					'updated_at'  => '2014-09-30 15:21:32.920618',
				),
			144 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '28.2                     ',
					'description' => 'Nominated Suppliers and their obligations: The Architect shall not nominate any Person as a Nominated Supplier against whom the Contractor makes a reasonable objection in accordance with Clause 28.3. The Contractor shall make such reasonable objection in writing not later than fourteen (14) Days from receipt of the nomination instruction from the Architect. The Architect shall not nominate (except where the Architect and Contractor otherwise agree) any Person who will not enter into a contract of sale which provides inter alia: 28.2(a) that the materials and goods to be supplied shall be of the quality and standard specified, provided always that where approval of the quality and standard of material is a matter of opinion of the Architect, such quality and standard shall be to reasonable satisfaction of the Architect. 28.2(b) that the Nominated Supplier shall make good by replacement or otherwise any defects in the material and good supplied which appear within the Defects Liability Period and shall bear any expenses reasonably incurred by the Contractor as a direct consequence of such defects provided always that: 28.2(b) (i) where the material and goods have been used or fixed, such defects are not such that examination by the Contractor ought to have revealed them before using or fixing; or 28.2(b) (ii) such defects are due solely to defective workmanship, materials and goods supplier and not caused by misuse, improper storage or any act neglect by the Contractor. 28.2(c) that the delivery of the materials and goods supplied shall commence and be completed in accordance with a delivery programme to be agreed between the Contractor and Supplier, or at such times as the Contractor may reasonably direct; 28.2(d) that the ownership of materials and goods shall pass to the Contractor upon delivery by the Nominated Supplier, whether or not payment has been made in full; and 28.2(e) that payment to Nominated Supplier shall be made within seven (7) Days after the Period of Honouring Certificates and shall be subject to the retention by the Contractor under Clause 28.5.',
					'priority'    => 143,
					'created_at'  => '2014-09-30 15:21:32.921315',
					'updated_at'  => '2014-09-30 15:21:32.921315',
				),
			145 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '28.3                     ',
					'description' => 'Objection to nomination of suppliers: Subject to Clause 28.4, the Contractor shall not be required to enter into a supply contract with any Nominated Supplier against whom the Contractor has made a reasonable objection based on the available known facts and documented evidence that the financial standing or solvency or technical competence of the Nominated Supplier is such that a prudent contractor, having regard to the scope of the supply contract would be justified in rejecting the nomination.',
					'priority'    => 144,
					'created_at'  => '2014-09-30 15:21:32.922208',
					'updated_at'  => '2014-09-30 15:21:32.922208',
				),
			146 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '28.4                     ',
					'description' => 'Action following objection of suppliers: Where such reasonable objection is made, the Architect may either issue further instruction to remove the objection so that the Contractor can enter into the supply contract or cancel such nomination or instruction and issue an instruction omitting the materials and goods which was the subject  of the nomination instruction or re-nominate another Nominated Supplier.',
					'priority'    => 145,
					'created_at'  => '2014-09-30 15:21:32.922919',
					'updated_at'  => '2014-09-30 15:21:32.922919',
				),
			147 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '28.5                     ',
					'description' => 'Value of materials and goods supplied by Nominated Suppliers: The Architect shall direct the Contractor as to the total value of materials and goods supplied by a Nominated Supplier which has been included in any certificate issued under Clause 30.0 and shall at the same time when the certificates are issued, inform the Nominated Supplier in writing of the amount of the said total. The Contractor shall retain from the sums included for the value of materials and goods the percentage of such value stated in the Appendix as Percentage of Certified Value Retained up to an amount not exceeding five (5) percent of the Nominated Supplier’s sum. The Contractor’s interest in any sums so retained shall be fiduciary as trustee for the Nominated Supplier (but without obligation to invest); and the Contractor’s beneficial interest in such sums shall be subject only to the right of the Contractor to have resource from time to time for payment of any amount which he is entitled under the nominated supply contract to deduct from any sum due or to become due to the Nominated Supplier. Upon the Architect having certified the release of the Retention Fund under Clause 30.6, such sums shall be release to the Nominated Supplier within seven (7) Days after the Period of Honouring Certificate and that if and when such sums are released to the Nominated Supplier, they shall be paid in full.',
					'priority'    => 146,
					'created_at'  => '2014-09-30 15:21:32.923633',
					'updated_at'  => '2014-09-30 15:21:32.923633',
				),
			148 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '28.6                     ',
					'description' => 'Payment to Nominated Suppliers: All payment is respect of the value of materials and goods supplied by a Nominated Supplier shall be made within seven (7) Days after the Period of Honouring Certificates and shall be subject to the retention by the Contractor under Clause 28.5',
					'priority'    => 147,
					'created_at'  => '2014-09-30 15:21:32.924375',
					'updated_at'  => '2014-09-30 15:21:32.924375',
				),
			149 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '28.7                     ',
					'description' => 'Contractor’s liability for Nominated Suppliers: The Contractor shall be fully responsible for any negligence, omission default and/or breach of contract by the Nominated Supplier and the Employer shall in no circumstances be liable to the Contractor.',
					'priority'    => 148,
					'created_at'  => '2014-09-30 15:21:32.925034',
					'updated_at'  => '2014-09-30 15:21:32.925034',
				),
			150 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '28.8                     ',
					'description' => 'Employer no privity of Contract with Nominated Suppliers: Neither the existence of or the exercise of the foregoing provisions nor anything else contained in the Contract shall create a privity of contract between the Employer and any of the Nominated Suppliers.',
					'priority'    => 149,
					'created_at'  => '2014-09-30 15:21:32.925691',
					'updated_at'  => '2014-09-30 15:21:32.925691',
				),
			151 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '29.1                     ',
					'description' => 'Works by Employer’s craftsmen: The Contractor shall permit the execution of work not forming part of the Contract on the Works by craftsmen, tradesmen or other contractors engaged by the Employer. Such craftsmen, tradesmen or other contractors engaged by the Employer shall be deemed to be a Person for whom the Employer is responsible and not to be a sub-contractor of the Contractor.',
					'priority'    => 150,
					'created_at'  => '2014-09-30 15:21:32.926425',
					'updated_at'  => '2014-09-30 15:21:32.926425',
				),
			152 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.1                     ',
					'description' => 'Payment application and issuance of Architect’s certificate: The Contractor shall submit a payment application at the Interim Claim Interval stated in the Appendix with complete details and particulars as required by the Architect and Quantity Surveyor, to enable them to consider and ascertain the amount to be included in an Interim Certificate. Upon receipt of Contractor’s details and particulars, the Architect after having received the payment valuation from the Quantity Surveyor shall, within twenty one (21) Days from the date of receipt of the Contractor’s application, issue an Interim Certificate to the Employer with a copy to the Contractor, and the Employer shall thereafter pay the amount certified to the Contractor within the Period of Honouring Certificates. Any failure by the Contractor to submit a payment application shall be deemed to be a waiver of his contractual entitlement for that Interim Certificate, and the Architect may or may not issue an Interim Certificate under the circumstances. After the issuance of the Certificate of Practical Completion, Interim Certificates shall be issued as and when further amounts are ascertained by the Architect and Quantity Surveyor as payable to the Contractor by the Employer.',
					'priority'    => 151,
					'created_at'  => '2014-09-30 15:21:32.92737',
					'updated_at'  => '2014-09-30 15:21:32.92737',
				),
			153 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.2                     ',
					'description' => 'Amount due in Architect’s certificate: The amount stated as due in an Interim Certificate shall, subject to any agreement between the parties as to stage payments, be the total value of the work properly executed and include the percentage of the value of materials and goods stated in the Appendix up to the date of the Contractor’s payment application less any amount which may be retained by the Employer under Clauses 30.5 and 30.6 and, less the amounts previously certified under Clauses 30.1. The materials and goods must be for incorporation into the permanent works and have been delivered to and properly stored at the Site and be protected against loss, damages or deterioration, and be in accordance with the Contract. The certificate shall only include the value of materials and goods which are reasonably, properly and not prematurely brought to the Site.',
					'priority'    => 152,
					'created_at'  => '2014-09-30 15:21:32.928265',
					'updated_at'  => '2014-09-30 15:21:32.928265',
				),
			154 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.3                     ',
					'description' => 'Errors in payment certificate: Save for clerical, computational or typographical error or errors of a similar nature, the Architect shall not be entitled to revise or correct any payment certificate issued by him under the Contract. Provided always that the Architect may, by a later certificate, make correction or notification in respect of any valuation errors in any earlier certificate.',
					'priority'    => 153,
					'created_at'  => '2014-09-30 15:21:32.92903',
					'updated_at'  => '2014-09-30 15:21:32.92903',
				),
			155 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.4                     ',
					'description' => 'Set-Off by Employer: The Employer shall be entitled to set-off all cost incurred and loss and expenses where it is expressly provided under Clauses 2.4, 4.4, 5.1, 6.5(e), 6.7, 14.4, 15.3(b), 15.3(c), 15.4, 15.5, 19.5 and 20.A.3. No set-off under this clause may be made unless: 30.4(a) the Architect or Quantity Surveyor (on behalf of the Employer) has submitted to the Contractor complete details of their assessment of such set-off; and 30.4(b) the Employer or the Architect on his behalf has given the Contractor a written notice delivered by hand or by registered post, specifying his intention to set-off the amount and the grounds on which such set-off is made. Unless expressly stated elsewhere, such written notice shall be given not later than twenty eight (28) Days before any set-off is deducted from any payment by the Employer. Any set-off by the Employer shall be recoverable from the Contractor as a debt or from any monies due or to become due to the Contractor under the Contract and/or from the Performance Bond. If the Contractor after receipt of the written notice from the Employer on the Architect on his behalf, disputes the amount of set-off, the Contractor shall within twenty one (21) Days of receipt of such written notice, send to the Employer delivered by hand or by registered post a statement setting out the reasons and particulars for such disagreement. If the parties are unable to agree on the amount of set-off within a further twenty one (21) Days after the receipt of the Contractor’s response, either party may refer the dispute to adjudication under Clause 34.1. The Employer shall not be entitled to exercise any set-off unless the amount has been agreed by the Contractor or the adjudicator has issued his decision.',
					'priority'    => 154,
					'created_at'  => '2014-09-30 15:21:32.929801',
					'updated_at'  => '2014-09-30 15:21:32.929801',
				),
			156 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.5                     ',
					'description' => 'Retention Fund: The Employer may retain the percentage of the total value of the work, materials and goods referred to in Clauses 30.2, which is stated in the Appendix as Percentage of Certified Value Retained. When the sum of the amounts so retained equals the amount stated in the Appendix as Limit of Retention Fund or that amount as reduced under Clauses 16.1(d) and 16.1(f) and/or Clauses 27.7, as the case may be, then no further amounts shall be retained by virtue of this clause.',
					'priority'    => 155,
					'created_at'  => '2014-09-30 15:21:32.930596',
					'updated_at'  => '2014-09-30 15:21:32.930596',
				),
			157 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.6                     ',
					'description' => 'Rules regarding Retention Fund: The amount retained under Clause 30.5 shall be subjected to the following rules: 30.6(a) the Employer’s interest in any amount so retained shall be fiduciary as trustee for the Contractor, Nominated Sub-Contractors and Nominated Suppliers (but without obligation to invest) and the Contractor’s, Nominated Sub-Contractors’ and Nominated Suppliers’ beneficial interest shall be subject only to the right of the Employer to have recourse from time to time for payment of any amount as the Architect may certify that he is entitled under the Contract to deduct from such sum due or to become due to the Contractor Nominated Sub-Contractors and Nominated Suppliers. In the event any of the party elects to demand in writing from the Employer (with a copy to the Architect) for such Retention Fund to be paid into a trust account, such fund shall be paid by the Employer within fourteen (14) Days into an escrow account to be held by a stakeholder appointed by the party making the application. All incidental costs of setting up such a trust account shall be borne by the Contractor or Nominated Sub-Contractors or Nominated Suppliers as the case may be; 30.6(b) when the Employer exercise any right under the Contract to deduct from any monies due to or become due to the Contractor or where applicable, the Nominated Sub-Contractors or Nominated Suppliers, he shall inform the relevant party in writing of the reason for that deduction; 30.6(c) upon the issuance of the Certificate of Practical Completion, the Architect shall within fourteen (14) Days issue a certificate for the release of one half of the Retention Fund and the Contractor shall be entitled to payment thereafter within the Period of Honouring Certificates; 30.6(d) upon the issuance of the Certificate of Making Good Defects, the Architect shall within fourteen (14) Days issue a certificate for the residue of the amount then so retained and the Contractor shall be entitled to payment within the Period of Honouring Certificates.',
					'priority'    => 156,
					'created_at'  => '2014-09-30 15:21:32.931311',
					'updated_at'  => '2014-09-30 15:21:32.931311',
				),
			158 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.7                     ',
					'description' => 'Suspension of Works for non-payment: Without prejudice to the Contractor’s right to determine his own employment under Clauses 26.0, if the Employer fails or neglects to pay the Contractor the amount due as shown in the payment certificate (less any Liquidated Damages and set-off which the Employer is expressly entitled to make under the Contract) and continue such default for fourteen (14) Days from the receipt of a written notice delivered by hand or registered post from the Contractor stating that if payment is not made within fourteen (14) Days, the Contractor may be a further written notice delivered by hand or registered post, forthwith suspend the execution of the Works until such time payment is made. Provided always that such notice shall not be given unreasonably or vexatiously.',
					'priority'    => 157,
					'created_at'  => '2014-09-30 15:21:32.932192',
					'updated_at'  => '2014-09-30 15:21:32.932192',
				),
			159 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.8                     ',
					'description' => 'Compulsory suspension of Works: If the Architect and/or Consultant inform the Contractor in writing of their withdrawal form the supervision of the execution of the Works required under the local building by-laws for whatever reasons, the Contractor shall forthwith suspend the execution of the Works and continue such suspension until the resumption of the said supervision.',
					'priority'    => 158,
					'created_at'  => '2014-09-30 15:21:32.932915',
					'updated_at'  => '2014-09-30 15:21:32.932915',
				),
			160 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.9                     ',
					'description' => 'Cessation insurance resulting from suspension of the Works: If the Contractor suspends the Works in accordance with the provisions of Clauses 30.7 and 30.8, he shall secure and protect the Works during the period of suspension and ensure that there is separate cessation insurance cover for all the risks specified in Clauses 19.0 and 20.A or 20.B or 20.C for the whole period of suspension. The cost incurred for such protection and cessation insurance cover shall be added to the Contract Sum.',
					'priority'    => 159,
					'created_at'  => '2014-09-30 15:21:32.93359',
					'updated_at'  => '2014-09-30 15:21:32.93359',
				),
			161 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '32.1                     ',
					'description' => 'Procedures following war damage: In the event of the Works of any unfixed materials and good intended for, delivered to and placed on or adjacent to the Works sustain war damage then notwithstanding anything expressed or implied elsewhere in the Contract: 32.1(a) the occurrence of such war damage shall be disregarded in computing any amount payable to the Contractor under or by virtue of the Contract. 32.1(b) the architect may issue AI requiring the Contractor to remove and / or dispose of any debris and/or damaged work and/or to execute such protective work as specified; 32.1(c) the Contractor shall reinstate or make good such war damage and shall proceed with the carrying out and completion of the Works, and the Architect shall grant to the Contractor a fair and reasonable extension of time for the completion of the Works; and 32.1(d) the removal and disposal of debris or damaged work, the execution of protective works and the reinstatement and making good of such war damage shall be deemed to be Variation required by the Architect.',
					'priority'    => 172,
					'created_at'  => '2014-09-30 15:21:32.942852',
					'updated_at'  => '2014-09-30 15:21:32.942852',
				),
			162 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.10                    ',
					'description' => 'Final Account: Within six (6) Months after Practical Completion of the Works, the Contractor shall send to the Architect and Quantity Surveyor, all documents necessary for preparing the Final Account, including all documents relating to the accounts of Nominated Sub-Contractors (if these had not been submitted earlier under the Nominated Sub-Contract) and Nominated Suppliers. Such documents shall contain all the latest construction drawings and details (bound together), details of all quantities, rates and prices and any adjustment of the Contract Sum and additional payment or compensation claimed by the Contractor under the Contract together with any explanation and supporting vouchers, documents and calculations, which may be necessary to enable the Final Account to be prepared by the Architect and Quantity Surveyor. The Final Account shall be completed within six (6) Months from receipt of all documents from the Contractor. The period for completion of the Final Account shall be adjusted if there is any delay by the Contractor in sending the necessary documents. In the event the Contractor fails to submit all documents necessary for preparing the Final Account, the Architect or Quantity Surveyor shall nevertheless complete and issue the same based on the information available within the Period to complete the Final Account stated in the Appendix. On completion of the Final Account, the Architect or Quantity Surveyor shall then send a copy of the document to the Employer and Contractor. 30.10(a) If nothing in the said Final Account is disputed by the Employer or Contractor within three (3) Months from the date of receipt of the Final Account from the Architect or Quantity Surveyor, the Final Account shall be conclusive and deemed agreed by the parties. 30.10(b) If either party disputes the Final Account, the party disputing the Final Account shall be written notice to the other party (with copies to the Architect and Quantity Surveyor) set out any disagreement complete with particulars within three (3) Months of the date of receipt of the Final Account from the Architect or Quantity Surveyor. The Architect or Quantity Surveyor within three (3) Months from the date of receipt of the grounds of dispute shall either amend or not amend the Final Account. Any party disagreeing with the amended Final Account or decision not to amend the Final Account shall refer the dispute to arbitration under Clauses 34.0 within three (3) Months from the date of receipt of the amended Final Account or decision not to amend the Final Account. Failure to refer the dispute to arbitration within the stipulated time, the Final Account or amended Final Account shall deem to be conclusive and agreed by the parties. 30.10(c) Any dispute on Liquidated Damages, set-off and interest which the Employer is entitled to make under the Contract shall referred to arbitration.',
					'priority'    => 160,
					'created_at'  => '2014-09-30 15:21:32.934294',
					'updated_at'  => '2014-09-30 15:21:32.934294',
				),
			163 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.11                    ',
					'description' => 'Items in Final Account: The Final Account of the Works shall show: 30.11(a) the adjustment made to the Contract Sum; 30.11(b) the amounts to which the Architect considers that the Contractor is entitled under the express provisions of the Contract; 30.11(c) the omission of all P.C.Sums and the related profit provided by the Contractor is the Contract Documents and the substitution of the amounts payable by the Employer to the Nominated Sub-Contractors and Nominated Suppliers together with the pro-rata amount for profit; and 30.11(d) the adjustment of Provisional Sums and omission of any Provisional Sums if not expended. The following shall not be included in the Final Account and are matters to be resolved separately between the Employer and Contractor: 30.11(e) any Liquidated Damages imposed by the Employer under Clause 22.1; 30.11(f) set-off by the Employer where it is expressly provided in the Contract under Clauses 30.4; and 30.11(g) interest payable by either of the parties to other party under Clause 30.17',
					'priority'    => 161,
					'created_at'  => '2014-09-30 15:21:32.935347',
					'updated_at'  => '2014-09-30 15:21:32.935347',
				),
			164 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.12                    ',
					'description' => 'Conclusiveness of the Final Account : Unless a written notice for arbitration shall have been given under Clause 34.0 by either party within the stipulated time stated in Clause 30.10, the Final Account or the last amended Final Account shall be conclusive and deemed agreed by the parties other than any outstanding items to be resolved separately between the Employer and Contractor under Clauses 30.11(e) to 30.11(g), except where the Final Account is erroneous by reason of: 30.12(a) fraud, dishonesty or fraudulent conceal relating to the Works; or 30.12(b) any arithmetical errors in any computation',
					'priority'    => 162,
					'created_at'  => '2014-09-30 15:21:32.936071',
					'updated_at'  => '2014-09-30 15:21:32.936071',
				),
			165 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.13                    ',
					'description' => 'Issuance of Penultimate Certificate: The Architect may issue a Penultimate Certificate for the release of the retention sums and other outstanding sums for all Nominated Sub-Contractors and/or Nominated Suppliers not later than fourteen (14) Days after the Certificate of Making Good Defects has been issued.',
					'priority'    => 163,
					'created_at'  => '2014-09-30 15:21:32.93675',
					'updated_at'  => '2014-09-30 15:21:32.93675',
				),
			166 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.14                    ',
					'description' => 'Issuance of Final Certificate: The Final Certificate shall be issued: 30.14(a) within twenty one (21) Days after the Period Honouring Certificates for the payment of the Penultimate Certificate; or 30.14(b) within twenty eight (28) Days after the Certificate of Making Good Defects has been issued, in the event no Penultimate Certificate has been issued.',
					'priority'    => 164,
					'created_at'  => '2014-09-30 15:21:32.937409',
					'updated_at'  => '2014-09-30 15:21:32.937409',
				),
			167 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.15                    ',
					'description' => 'Final Certificate: The Final Certificate shall state: 30.15(a) the Final Account; less 30.15(b) the total sums certified in previous payment certificates (whether paid or not paid) to the Contractor; and the difference, if any, between the sums shall be the balance due to the Contractor by the Employer or conversely as the case may be. The balance shall be payable by the Employer to the Contractor within the Period of Honouring Certificates or if it is a debt payable by the Contractor to the Employer, shall be payable by the Contractor within the Period of Honouring Certificates. The Architect shall not be obliged to issue the Final Certificate before the issuance of a Certificate of Making Good Defects.',
					'priority'    => 165,
					'created_at'  => '2014-09-30 15:21:32.938077',
					'updated_at'  => '2014-09-30 15:21:32.938077',
				),
			168 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.16                    ',
					'description' => 'Final Certificate not conclusive: The Final Certificate shall be conclusive on the final value of the Works with the exception of any outstanding claims between the Employer and Contractor under Clause 30.11. The Final Certificate shall not be conclusive evidence that any work, materials and goods to which it relates and designs (if any) executed by the Contractor and/or Nominated Sub-Contractors are in accordance with the Contract.',
					'priority'    => 166,
					'created_at'  => '2014-09-30 15:21:32.938769',
					'updated_at'  => '2014-09-30 15:21:32.938769',
				),
			169 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '30.17                    ',
					'description' => 'Interest: If the Employer fails to pay the Contractor the amount due on any certificate (less and Liquidated Damages and set-off which the Employer is expressly entitled to make under the Contract) after the Period of Honouring Certificates, or the Contractor owes a debt or fails to pay any sum due and owing to the Employer within twenty one (21) Days after receipt of written notification by the Employer of such debt or amount owing, a simple interest based on the Maybank Base Lending Rate plus one (1) percent shall be payable by the defaulting party on such outstanding amount until the date payment is made.',
					'priority'    => 167,
					'created_at'  => '2014-09-30 15:21:32.939436',
					'updated_at'  => '2014-09-30 15:21:32.939436',
				),
			170 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '31.1                     ',
					'description' => 'Hostilities – determination by Employer or Contractor: If during the currency of the Contract there is an outbreak of hostilities (whether war is declared or not) in which Malaysia is involved on a scale involving the general mobilisation of the Malaysian Armed Forces in which the Works are to be carried out, then either the Employer or Contractor may at any time by written notice delivered by hand or by registered post to the other, forthwith determine the employment of the Contractor under the Contract.',
					'priority'    => 168,
					'created_at'  => '2014-09-30 15:21:32.940121',
					'updated_at'  => '2014-09-30 15:21:32.940121',
				),
			171 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '31.2                     ',
					'description' => 'Notices of determination: Provided always that such written notice shall not be given: 31.2(a) before the expiration of twenty eight (28) Days from the date on which the order is given for general mobilisation as aforesaid; or 31.2(b) after Practical Completion of the Works unless the Works have sustained war damage as define in Clause 32.2.',
					'priority'    => 169,
					'created_at'  => '2014-09-30 15:21:32.9408',
					'updated_at'  => '2014-09-30 15:21:32.9408',
				),
			172 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '31.3                     ',
					'description' => 'AI regarding protective work: After a written notice under Clause 31.1 has been given by either the Contractor or the Employer, the Architect may within fourteen (14) Days issue AI to the Contractor requiring the execution of protective work and the Contractor shall comply with such AI, as if written notice of determination has not been given. If the Contractor for reasons beyond his control is prevented from completing the work to which the said AI relate within three (3) months from the date on which the AI was issued, he may abandon such work.',
					'priority'    => 170,
					'created_at'  => '2014-09-30 15:21:32.941494',
					'updated_at'  => '2014-09-30 15:21:32.941494',
				),
			173 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(d)           ',
					'description' => 'delay on the part of craftsmen, tradesmen or other contractors employed or engaged by the Employer in executing work not forming part of the Contract or the failure to execute such work;',
					'priority'    => 3,
					'created_at'  => '2014-09-30 15:21:45.606866',
					'updated_at'  => '2014-09-30 15:21:45.606866',
				),
			174 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '32.2                     ',
					'description' => 'Definition of war damage: The expression “war damage” means: 32.2(a) damage occurring (whether accidentally or not) as the direct result of action taken by the enemy or action taken in combating the enemy or in repelling an attack by the enemy; 32.2(b) damage occurring (whether accidentally or not) as a direct result of measures taken under proper authority to avoid the spreading of or otherwise to mitigate, the consequence of such damage as aforesaid; 32.2(c) accidental damage occurring as the direct result of any precautionary or preparatory measures taken under proper authority with a view to preventing or hindering the carrying out of any attack by the enemy or of precautionary or preparatory measures involving the doing of work in anticipation of enemy action involving a substantial degree of risk to property.',
					'priority'    => 173,
					'created_at'  => '2014-09-30 15:21:32.943569',
					'updated_at'  => '2014-09-30 15:21:32.943569',
				),
			175 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '33.1                     ',
					'description' => 'Antiquities property of Employer: All fossils, antiquities and other objects of interest or value which may be found on the Site or in excavating the same during the progress of the Works shall become the property of the Employer. Upon discovery of such objects the Contractor shall forthwith cease work and shall not disturb the object and take all necessary precautions to preserve the object in the exact position and condition it was discovered. He shall immediately notify the Architect or the Site Staff of the discovery and the Architect shall issue written instruction in this regard to what has to be done.',
					'priority'    => 174,
					'created_at'  => '2014-09-30 15:21:32.94432',
					'updated_at'  => '2014-09-30 15:21:32.94432',
				),
			176 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.1                     ',
					'description' => 'Set-off disputes referred to adjudication: Reference to adjudication is a condition precedent to arbitration for a disputes under clause 30.4. The parties by written agreement are free to refer any other disputes to adjudication. Any dispute under Clause 30.4 after the date of Practical Completion shall be referred to arbitration under Clause 34.5',
					'priority'    => 175,
					'created_at'  => '2014-09-30 15:21:32.944999',
					'updated_at'  => '2014-09-30 15:21:32.944999',
				),
			177 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.2                     ',
					'description' => 'Notice to refer to adjudication: Where a party requires a dispute or difference under Clause 34.1 to be referred to adjudication, such disputes or differences shall be referred to an adjudicator to be agreed between the parties. If after the expiration of twenty one (21) Days from the date of the written notice to concur on the appointment of the adjudicator, there is failure to agree on the appointment, the party initiating the adjudication shall be apply to the President of Pertubuhan Akitek Malaysia to appoint an adjudicator, and such adjudicator so appointed shall be deemed to appointed with the agreement and consent of the parties to the contract.',
					'priority'    => 176,
					'created_at'  => '2014-09-30 15:21:32.94566',
					'updated_at'  => '2014-09-30 15:21:32.94566',
				),
			178 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.3                     ',
					'description' => 'Adjudication Rules: Upon appointment, the adjudicator shall be initiate the adjudication in accordance with the current edition of the PAM Adjudication Rules or any modification or revision to such rules.',
					'priority'    => 177,
					'created_at'  => '2014-09-30 15:21:32.946347',
					'updated_at'  => '2014-09-30 15:21:32.946347',
				),
			179 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.4                     ',
					'description' => 'Decision of the adjudication: If a party disputes the adjudicator’s decision, he shall nevertheless be bound by the adjudicator’s decision until Practical Completion but shall give a written notice to the other party to refer the dispute which was the subject of the adjudication to arbitration within six (6) Weeks from the date of the adjudicator’s decision. The adjudicator’s decision shall be final and binding on the parties if the dispute on the adjudicator’s decision is not referred to arbitration within the stipulated time. The parties may settle any dispute with the adjudicator’s decision by written agreement between the parties or by arbitration under Clause 34.5',
					'priority'    => 178,
					'created_at'  => '2014-09-30 15:21:32.947013',
					'updated_at'  => '2014-09-30 15:21:32.947013',
				),
			180 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.5                     ',
					'description' => 'Disputes referred to arbitration: In the event that any dispute or difference arise between the Employer and Contractor, either during the progress or after completion or abandonment of the Works regarding: 34.5(a) any matter of whatsoever natures arising under or in connection with the Contract; 34.5(b) any matter left by the Contract to the discretion of the Architect. 34.5(c) the withholding by the Architect of any certificate to which the contractor may claim to be entitled to; 34.5(d) the right and liabilities if the parties under Clause 25.0, 26.0, 31.0or 32.0; or 34.5(e) the unreasonable withholding of consent or agreement by the Employer or Contractor, then such disputes or differences shall be referred to arbitration',
					'priority'    => 179,
					'created_at'  => '2014-09-30 15:21:32.947722',
					'updated_at'  => '2014-09-30 15:21:32.947722',
				),
			181 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.6                     ',
					'description' => 'Procedures for appointment of arbitrator: Upon the disputes or difference having arising then: 34.6(a) any party may serve written notice on the others party that such  disputes or differences shall be referred to an arbitrator to be agreed between the parties; and 34.6(b) if after expiration of twenty one (21) Days from the date of the written notice to concur on the appointment of the arbitrator, there is a failure to agree on the appointment, the party initiating the arbitration shall apply to the President of Pertubuhan Akitek Malaysia to appoint an arbitrator, and such arbitrator so appointed shall be deemed to appointment with the agreement and consent of the parties to the contract.',
					'priority'    => 180,
					'created_at'  => '2014-09-30 15:21:32.948413',
					'updated_at'  => '2014-09-30 15:21:32.948413',
				),
			182 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.7                     ',
					'description' => 'Arbitration Act and Rules: Upon appointment, the arbitrator shall initiate the arbitration proceeding in accordance with the provisions of the Arbitration Act 2005 or any statutory modification or re-enactment to the Act and the PAM Arbitration Rules or any modification or revision to such rules',
					'priority'    => 181,
					'created_at'  => '2014-09-30 15:21:32.949095',
					'updated_at'  => '2014-09-30 15:21:32.949095',
				),
			183 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.8                     ',
					'description' => 'Powers of arbitrator: The arbitrator shall without prejudice to the generality of his powers, have power: 34.8(a) to rectify the Contract so that it accurately reflects the true agreement made by the Employer and Contractor: 34.8(b) to direct such measurement and/or valuations as may in his opinion be desirable in order to determine the right of the parties; 34.8(c) to ascertain and award any sum which ought to have been the subject of or included in any certificate; 34.8(d) to open up, review and revise any certificate, opinion, decision, requirement, or notice; 34.8(e) to determine all matter in dispute submitted to him in the same manners as if no such certificate, opinion, decision, requirement or notice had been given; 34.8(f) to award interest from such dates at such rates and with such rests as he think fit: 34.8(f) (i) on the whole or part of any amount awarded by him in respect of any period up to the date of the award; 34.8(f) (ii) on the whole or part of any amount claimed in the arbitration and outstanding at the commencement of the arbitral proceedings but paid before the award was made, in respect of any period up to date of payment; and 34.8(g) to award interest from the date of award ( or any later date) until payment, as such rates and with such rests as he thinks fit on the outstanding amount of any award.',
					'priority'    => 182,
					'created_at'  => '2014-09-30 15:21:32.949775',
					'updated_at'  => '2014-09-30 15:21:32.949775',
				),
			184 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.9                     ',
					'description' => 'Consolidation of arbitration proceeding: Where any dispute arises between the Employer and Contractor and the dispute relates to the works of a Nominated Sub-Contractor and arises out of or is connected with the same dispute between the Contractor and such Nominated Sub-Contractor, the Employer and Contractor shall use their best endeavour to appoint the same arbitrator to hear the dispute under Clause 29.3 of the PAM sub-contract 2006.',
					'priority'    => 183,
					'created_at'  => '2014-09-30 15:21:32.950496',
					'updated_at'  => '2014-09-30 15:21:32.950496',
				),
			185 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(e)           ',
					'description' => 'delay or failure in the supply of materials and goods which the Employer had agreed to supply for the Works',
					'priority'    => 4,
					'created_at'  => '2014-09-30 15:21:45.607604',
					'updated_at'  => '2014-09-30 15:21:45.607604',
				),
			186 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.10                    ',
					'description' => 'Commencement of arbitration proceeding: Unless with the written agreement of the Employer and Contractor, such arbitration proceeding shall not commence until after Practical Completion or alleged Practical Completion of the Works or determination or alleged determination of the Contractor’s employment under the Contract or abandonment of the Works except on: 34.10(a) the question of whether or not the issuance of an instruction is empowered by these conditions; 34.10(b) any dispute or difference under clause 31.0 and 32.0; 34.10(c) whether or not a certificate has been improperly withheld or not in accordance with these Conditions; or 34.10(d) whether or not a payment to which the Contractor may claim to be entitled has been properly withheld in accordance with these conditions.',
					'priority'    => 184,
					'created_at'  => '2014-09-30 15:21:32.951173',
					'updated_at'  => '2014-09-30 15:21:32.951173',
				),
			187 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '34.11                    ',
					'description' => 'Arbitrator’s award to be final and binding on parties: The award of such arbitrator shall be final and binding on the parties. ',
					'priority'    => 185,
					'created_at'  => '2014-09-30 15:21:32.951911',
					'updated_at'  => '2014-09-30 15:21:32.951911',
				),
			188 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '35.1                     ',
					'description' => 'Mediation under PAM rules: Notwithstanding Clause 34.0 of these Conditions, upon the written agreement of both the Employer and Contractor, the parties may refer any dispute for mediation. If the parties fail to agree on a mediator after twenty one (21) Days from the date of the written agreement to refer to dispute to mediation, any party can apply to the President of Pertubuhan Akitek Malaysia to appoint a mediator. Upon appointment, the mediator shall initiate the mediation in accordance with the PAM Mediation Rules or any modification or revision to such rules.',
					'priority'    => 186,
					'created_at'  => '2014-09-30 15:21:32.952568',
					'updated_at'  => '2014-09-30 15:21:32.952568',
				),
			189 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '35.2                     ',
					'description' => 'Mediation does not prejudice the parties’ right to arbitration: Prior reference of the dispute to mediation under Clause 35.1 shall not be a condition precedent for its reference to adjudication or either the Contractor or the Employer, nor shall any their rights to refer the dispute to adjudication or arbitration under Clause 34.0 of these Condition be in any way prejudiced or affected by this clause.',
					'priority'    => 187,
					'created_at'  => '2014-09-30 15:21:32.953243',
					'updated_at'  => '2014-09-30 15:21:32.953243',
				),
			190 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '36.1                     ',
					'description' => 'Notice: Any written notice or others document to be given under the Contract shall be given or send by: 36.1(a) hand; 36.1(b) ordinary mail or registered post; or 36.1(c) facsimile transmission',
					'priority'    => 188,
					'created_at'  => '2014-09-30 15:21:32.953956',
					'updated_at'  => '2014-09-30 15:21:32.953956',
				),
			191 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '36.2                     ',
					'description' => 'Notice deem served: Any written notice or others document shall be deemed to have been duly served upon and received by the addressee: 36.2(a) if delivered by hand, at the time of delivery 36.2(b) if sent by ordinary mail or registered post, after (3) days of posting; or 36.2(c) if transmitted by way of facsimile transmission.',
					'priority'    => 189,
					'created_at'  => '2014-09-30 15:21:32.95461',
					'updated_at'  => '2014-09-30 15:21:32.95461',
				),
			192 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '36.3                     ',
					'description' => 'Proof of Notice: In proving the giving of a written notice or any others document under or respect of the Contract, it shall be sufficient to show: 36.3(a) in the case of hand delivery, a signed acknowledgement of receipt; 36.3(b) in the case of the registered post, a receipt of posting from the Post Office; or 36.3(c) in the case of facsimile transmission, that the facsimile transmission was duly transmitted from the dispatching terminal, as evidenced by a transmission report generated by the transmitting equipment.',
					'priority'    => 190,
					'created_at'  => '2014-09-30 15:21:32.955269',
					'updated_at'  => '2014-09-30 15:21:32.955269',
				),
			193 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '36.4                     ',
					'description' => 'Written communication: All written communication shall be sent to the address stated in the Article of Agreement unless otherwise notified in writing.',
					'priority'    => 191,
					'created_at'  => '2014-09-30 15:21:32.955936',
					'updated_at'  => '2014-09-30 15:21:32.955936',
				),
			194 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '37.1                     ',
					'description' => 'Submission of Performance Bond: The Contractor shall before the Date of Commencement of the Works, submit to the Employer a Performance Bond for a sum equivalent to the percentage stated in the Appendix as a security for the due performance and observance by the Contractor of his obligations under the Contract up to Practical Completion of the Works.',
					'priority'    => 192,
					'created_at'  => '2014-09-30 15:21:32.956585',
					'updated_at'  => '2014-09-30 15:21:32.956585',
				),
			195 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '37.2                     ',
					'description' => 'Form of the Performance Bond: The Performance Bond shall be in the form issued in the terms and conditions specified in the Contract or otherwise approved by the Employer.',
					'priority'    => 193,
					'created_at'  => '2014-09-30 15:21:32.957259',
					'updated_at'  => '2014-09-30 15:21:32.957259',
				),
			196 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '37.3                     ',
					'description' => 'Validity of the Performance Bond: The Performance Bond submitted by the Contractor shall remain valid until three (3) Months after the Completion Date. Where the Works would not be completed by the Completion Date, the Contractor shall before the expiry of the Performance Bond, extend the duration of the Performance Bond to expire three (3) Months after the projected Practical Completion of the Works.',
					'priority'    => 194,
					'created_at'  => '2014-09-30 15:21:32.957905',
					'updated_at'  => '2014-09-30 15:21:32.957905',
				),
			197 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '37.4                     ',
					'description' => 'Failure to extend the validity: If the Contractor fails to provide or maintain the validity of the Performance bond in accordance with this clause, then without prejudice to any other rights and remedies which the Employer may possess, the Employer shall be entitled to withhold or deduct an amount equal to the Performance Bond from any payment due or to become due to the Contractor.',
					'priority'    => 195,
					'created_at'  => '2014-09-30 15:21:32.958654',
					'updated_at'  => '2014-09-30 15:21:32.958654',
				),
			198 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '37.5                     ',
					'description' => 'Payments from the Performance Bond: In the event the Employer determines the employment of the Contractor in accordance with Clause 25.0, or if there is any breach of the Contract, the Employer may call on the Performance Bond and utilize and make payments out of or deduction from the Performance Bond for the completion of and/or rectification of the Works and reimbursement of loss, and/or expense suffered by the Employer. On completion of the Works, any balance of monies remaining from the Performance Bond shall be refunded to the Contractor without interest.',
					'priority'    => 196,
					'created_at'  => '2014-09-30 15:21:32.959417',
					'updated_at'  => '2014-09-30 15:21:32.959417',
				),
			199 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '37.6                     ',
					'description' => 'Return of Performance Bond: In the event the Contractor determines his own employment in accordance with Clause 26.0, the Employer shall within twenty eight (28) Days return the Performance Bond to the Contractor for cancellation.',
					'priority'    => 197,
					'created_at'  => '2014-09-30 15:21:32.960104',
					'updated_at'  => '2014-09-30 15:21:32.960104',
				),
			200 =>
				array(
					'clause_id'   => $main->id,
					'no'          => '38.1                     ',
					'description' => 'Governing Law: The law governing the Contract shall be the Laws of Malaysia.',
					'priority'    => 198,
					'created_at'  => '2014-09-30 15:21:32.960764',
					'updated_at'  => '2014-09-30 15:21:32.960764',
				),
			201 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(a)           ',
					'description' => 'the Contractor not having received in due time the necessary AI (including those for or in regard to the expenditure of P.C Sums and Provisional Sums, further drawings, details, levels and any other information) for which he had specifically applied in writing to the Architect. The Contractor’s application must be submitted to the Architect in sufficient time before the commencement of construction of the affected works, enable the Architect to issue the necessary AI within a period which would not materially affect the progress of the affected works, having regard to the Completion Date. Provided always that the AI was not required as a result of any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors',
					'priority'    => 0,
					'created_at'  => '2014-09-30 15:21:45.598686',
					'updated_at'  => '2014-09-30 15:21:45.598686',
				),
			202 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(b)           ',
					'description' => 'delay by the Employer in giving possession of the site or any section of the Site in accordance with Clauses 21.1 and 21.2;',
					'priority'    => 1,
					'created_at'  => '2014-09-30 15:21:45.605201',
					'updated_at'  => '2014-09-30 15:21:45.605201',
				),
			203 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(c)           ',
					'description' => 'compliance with a written instruction issued by the Architect in regard to the postponement or suspension of all or any part of the Works to be executed under Clause 21.4;',
					'priority'    => 2,
					'created_at'  => '2014-09-30 15:21:45.606075',
					'updated_at'  => '2014-09-30 15:21:45.606075',
				),
			204 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(f)           ',
					'description' => 'the opening up for inspection of any work covered up, testing any materials and good or executed work in accordance with Clause 6.3, unless the inspection or test showed that the works, materials and goods were not in accordance with the Contract or was in the opinion of the Architect required in consequence of some prior negligence, omission, default and/or breach of contract by the Contractor;',
					'priority'    => 5,
					'created_at'  => '2014-09-30 15:21:45.608283',
					'updated_at'  => '2014-09-30 15:21:45.608283',
				),
			205 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(g)           ',
					'description' => 'any act of prevention or breach of contract by the Employer;',
					'priority'    => 6,
					'created_at'  => '2014-09-30 15:21:45.609058',
					'updated_at'  => '2014-09-30 15:21:45.609058',
				),
			206 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(h)           ',
					'description' => 'delay as a result of a compliance with AI issued in connection with the discovery of antiquities under Clause 33.1;',
					'priority'    => 7,
					'created_at'  => '2014-09-30 15:21:45.609725',
					'updated_at'  => '2014-09-30 15:21:45.609725',
				),
			207 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(i)           ',
					'description' => 'appointment of a replacement Person under Articles 3, 4, 5 and 6;',
					'priority'    => 8,
					'created_at'  => '2014-09-30 15:21:45.610422',
					'updated_at'  => '2014-09-30 15:21:45.610422',
				),
			208 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(j)           ',
					'description' => 'compliance with a written instruction issued by the Architect in connection with disputes with neighbouring property owners provided always that same is not caused by negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractor;',
					'priority'    => 9,
					'created_at'  => '2014-09-30 15:21:45.611104',
					'updated_at'  => '2014-09-30 15:21:45.611104',
				),
			209 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(k)           ',
					'description' => 'by reason of the execution of work for which a Provisional Quantity is included in the Contract Bills which in the opinion of the Architect is not a reasonably accurate forecast of the quantity of work required;',
					'priority'    => 10,
					'created_at'  => '2014-09-30 15:21:45.611862',
					'updated_at'  => '2014-09-30 15:21:45.611862',
				),
			210 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(l)           ',
					'description' => 'failure of the Employer to give in due time entry to or exit from the Site or any part through or over any land, by way of passage adjoining or connected to the Site and in the possession or control of the Employer;',
					'priority'    => 11,
					'created_at'  => '2014-09-30 15:21:45.612795',
					'updated_at'  => '2014-09-30 15:21:45.612795',
				),
			211 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(m)           ',
					'description' => 'suspension by the Contractor of his obligations under Clauses 30.7 and 30.8; ',
					'priority'    => 12,
					'created_at'  => '2014-09-30 15:21:45.613613',
					'updated_at'  => '2014-09-30 15:21:45.613613',
				),
			212 =>
				array(
					'clause_id'   => $lAndE->id,
					'no'          => 'Clause 24.3(n)           ',
					'description' => 'suspension of the whole part of the Works by order of an Appropriate Authority provided always that the same is due to negligence or omission on the part of the Employer, Architect or Consultant.',
					'priority'    => 13,
					'created_at'  => '2014-09-30 15:21:45.614303',
					'updated_at'  => '2014-09-30 15:21:45.614303',
				),
			213 =>
				array(
					'clause_id'   => $ae->id,
					'no'          => 'Clause 11.6 (a)          ',
					'description' => 'Valuation rules: The valuation of Variations and work executed by the Contractor for which a Provisional Quantity is included in the Contract and the expenditure of Provisional Sums (other than for work for which a tender had been accepted under Clause 27.14) shall be made in accordance with the following rules: 11.6(a) where work is of a similar character to, is executed under similar conditions as, and does not significantly change the quantity of work as set out in the Contract Documents, the rates and prices in the Contract Documents shall determine the valuation; ',
					'priority'    => 0,
					'created_at'  => '2014-09-30 15:24:55.310765',
					'updated_at'  => '2014-09-30 15:24:55.310765',
				),
			214 =>
				array(
					'clause_id'   => $ae->id,
					'no'          => 'Clause 11.6 (b)          ',
					'description' => ' Valuation rules: The valuation of Variations and work executed by the Contractor for which a Provisional Quantity is included in the Contract and the expenditure of Provisional Sums (other than for work for which a tender had been accepted under Clause 27.14) shall be made in accordance with the following rules: 11.6 (b) where work is of a similar character to work as set out in the Contract Documents but is not executed under similar conditions or is executed under similar conditions but there is significant change in the quantity of work carried out, the rates and prices in the Contract Documents shall be the basis for determining the valuation which shall include a fair adjustment in the rates to take into account such difference. ',
					'priority'    => 1,
					'created_at'  => '2014-09-30 15:24:55.320249',
					'updated_at'  => '2014-09-30 15:24:55.320249',
				),
			215 =>
				array(
					'clause_id'   => $ae->id,
					'no'          => 'Clause 11.6 (c )         ',
					'description' => ' Valuation rules: The valuation of Variations and work executed by the Contractor for which a Provisional Quantity is included in the Contract and the expenditure of Provisional Sums (other than for work for which a tender had been accepted under Clause 27.14) shall be made in accordance with the following rules: 11.6 (c) where work is not of a similar character to work as set out in the Contract Documents, the valuation shall be at fair market rates and prices determined by the Quantity Surveyor; ',
					'priority'    => 2,
					'created_at'  => '2014-09-30 15:24:55.322442',
					'updated_at'  => '2014-09-30 15:24:55.322442',
				),
			216 =>
				array(
					'clause_id'   => $ae->id,
					'no'          => 'Clause 11.6 (d) (i)      ',
					'description' => 'Valuation rules: The valuation of Variations and work executed by the Contractor for which a Provisional Quantity is included in the Contract and the expenditure of Provisional Sums (other than for work for which a tender had been accepted under Clause 27.14) shall be made in accordance with the following rules: 11.6 (d) where work cannot be properly measured and valued in accordance with Clause 11.6(a), (b) or (c), the Contractor shall be allowed: 11.6(d) (i) the daywork rates in the Contract Documents; The vouchers specifying the time spent daily upon the work, the workers names, materials, additional construction plant, scaffolding and transport used shall be signed by the Site Agent and verified by the Site Staff and shall be delivered to the Architect and Quantity Surveyor at weekly intervals with the final records delivered not later than fourteen (14) Days after the work has been completed. ',
					'priority'    => 3,
					'created_at'  => '2014-09-30 15:24:55.324075',
					'updated_at'  => '2014-09-30 15:24:55.324075',
				),
			217 =>
				array(
					'clause_id'   => $ae->id,
					'no'          => 'Clause 11.6 (d) (ii)     ',
					'description' => 'Valuation rules: The valuation of Variations and work executed by the Contractor for which a Provisional Quantity is included in the Contract and the expenditure of Provisional Sums (other than for work for which a tender had been accepted under Clause 27.14) shall be made in accordance with the following rules: 11.6 (d) where work cannot be properly measured and valued in accordance with Clause 11.6(a), (b) or (c), the Contractor shall be allowed: 11.6(d) (ii) where there are no such daywork rates in the Contract Documents, at the actual cost to the Contractor of his materials, additional construction plant and scaffolding, transport and labour for the work concerned, plus fifthteen (15) percent, which percentage shall include for the use of all tools, standing plant, standing scaffolding, supervision, overheads and profit. The vouchers specifying the time spent daily upon the work, the workers names, materials, additional construction plant, scaffolding and transport used shall be signed by the Site Agent and verified by the Site Staff and shall be delivered to the Architect and Quantity Surveyor at weekly intervals with the final records delivered not later than fourteen (14) Days after the work has been completed. ',
					'priority'    => 4,
					'created_at'  => '2014-09-30 15:24:55.325661',
					'updated_at'  => '2014-09-30 15:24:55.325661',
				),
			218 =>
				array(
					'clause_id'   => $ae->id,
					'no'          => 'Clause 11.6 (e)          ',
					'description' => 'Valuation rules: The valuation of Variations and work executed by the Contractor for which a Provisional Quantity is included in the Contract and the expenditure of Provisional Sums (other than for work for which a tender had been accepted under Clause 27.14) shall be made in accordance with the following rules:11.6(e) the rates and prices in the Contract documents shall determine the valuation of items omitted. If omissions substantially vary the conditions under which any remaining items of work are carried put, the prices of such remaining items shall be valued under Clause 11.6 (a), (b) or (c)',
					'priority'    => 5,
					'created_at'  => '2014-09-30 15:24:55.327332',
					'updated_at'  => '2014-09-30 15:24:55.327332',
				),
			219 =>
				array(
					'clause_id'   => $ae->id,
					'no'          => 'Clause 11.6 (f)          ',
					'description' => 'Valuation rules: The valuation of Variations and work executed by the Contractor for which a Provisional Quantity is included in the Contract and the expenditure of Provisional Sums (other than for work for which a tender had been accepted under Clause 27.14) shall be made in accordance with the following rules: 11.6(f) in respect of Provisional Quantity, the quantities stated in the Contract Documents shall be re-measured by the Quantity Surveyor based on the actual quantities executed. The rates and prices in the Contract Documents shall determine their valuations.',
					'priority'    => 6,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			220 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(a)',
					'description' => 'Force Majeure',
					'priority'    => 0,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			221 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(b)',
					'description' => 'Exceptionally inclement weather',
					'priority'    => 1,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			222 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(c)',
					'description' => 'Loss and/or damage occasioned by one or more of the contingencies referred to in Clause 20.A, 20.B or 20.C as the case may be, provided always that the same is not due to any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors',
					'priority'    => 2,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			223 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(d)',
					'description' => 'Civil commotion, strike or lockout affecting any of the trades employed upon the Works or any of the trades engaged in the preparation, manufacture or transportation of any materials and goods required for the Works',
					'priority'    => 3,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			224 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(e)',
					'description' => 'The Contractor not having received in due time the necessary AI (including those for or in regard to the expenditure of P.C. Sums and Provisional Sums, further drawings, details, levels and any other information) for which he had specifically applied in writing to the Architect in sufficient time before the commencement of construction of the affected works, to enable the Architect to issue the necessary AI within a period which would not materially affect the progress of the affected works, having regard to the Completion Date. Provided always that the AI was not required as a result of any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors',
					'priority'    => 4,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			225 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(f)',
					'description' => 'Delay by the Employer in giving possession of the Site or any section of the Site in accordance with Clause 21.1 and 21.2',
					'priority'    => 5,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			226 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(g)',
					'description' => 'Compliance with AI issued by Architect under Clauses 1.4, 11.2 and 21.4',
					'priority'    => 6,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			227 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(h)',
					'description' => 'Delay on the part of Nominated Sub-Contractors for the reasons set out in Clauses 21.4(a) to 21.4(w) of the PAM Sub-Contract 2006',
					'priority'    => 7,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			228 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(i)',
					'description' => 'Re-nomination of Nominated Sub-Contractors as set out in Clause 27.11',
					'priority'    => 8,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			229 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(j)',
					'description' => 'Delay on the part of craftsmen, tradesmen or other contractors employed or engaged by the Employer in executing work not forming part of the Contract or the failure to execute such work',
					'priority'    => 9,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			230 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(k)',
					'description' => 'Delay or failure in the supply of materials and goods which the Employer had agreed to supply for the Works',
					'priority'    => 10,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			231 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(l)',
					'description' => 'The opening up for inspection of any work covered up, testing any materials, goods or executed work in accordance with Clause 6.3, unless the inspection or test: 23.8(l) (i) is provided for in the Contract Bills; 23.8(l) (ii) shows that the works, materials and goods were not in accordance with the contract; or 23.8(l) (iii) is required by the Architect in consequence of some prior negligence, omission, default and/or breach of contract by the Contractor',
					'priority'    => 11,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			232 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(m)',
					'description' => 'Any act of prevention or breach of contract by the Employer',
					'priority'    => 12,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			233 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(n)',
					'description' => 'War damage under Clause 32.1',
					'priority'    => 13,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			234 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(o)',
					'description' => 'Compliance with AI issued in connection with the discovery of antiquities under Clause 33.1',
					'priority'    => 14,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			235 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(p)',
					'description' => 'Compliance with any changes to any law, regulations, by-law or terms and conditions of any Appropriate Authority and Service Provider',
					'priority'    => 15,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			236 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(q)',
					'description' => 'Delay caused by any Appropriate Authority and Service Provider in carrying out, or failure to carry out their work which affects the Contractor’s work progress, provided always that such delay is not due to any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors',
					'priority'    => 16,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			237 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(r)',
					'description' => 'Appointment of a replacement Person under Articles 3, 4, 5 and 6',
					'priority'    => 17,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			238 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(s)',
					'description' => 'Compliance with AI issued in connection with disputes with neighbouring property owners provided always that such dispute is not caused by negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors',
					'priority'    => 18,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			239 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(t)',
					'description' => 'Delay as a result of the execution of work for which a Provisional Quantity is included in the contract Bills which in the opinion of the Architect is not a reasonably accurate forecast of the quantity of work required',
					'priority'    => 19,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			240 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(u)',
					'description' => 'Failure of the Employer to give in due time entry to or exit from the Site or any part through or over any land, by way passage adjoining or connected to the Site and in possession or control of the Employer',
					'priority'    => 20,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			241 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(v)',
					'description' => 'Suspension by the Contractor of his obligation under Clauses 30.7 and 30.8',
					'priority'    => 21,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			242 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(w)',
					'description' => 'Suspension of the whole or part of the Works by order of an Appropriate Authority provided the same is not due to any negligence, omission, default and/or breach of contract by the Contractor and/or Nominated Sub-Contractors',
					'priority'    => 22,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
			243 =>
				array(
					'clause_id'   => $eot->id,
					'no'          => 'Clause 23.8(x)',
					'description' => 'Any other ground for extension of time expressly stated in the Contract.',
					'priority'    => 23,
					'created_at'  => '2014-09-30 15:24:55.328631',
					'updated_at'  => '2014-09-30 15:24:55.328631',
				),
		));
	}

}
