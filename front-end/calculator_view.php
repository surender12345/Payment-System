<div class="payment-system-calculator">
	<div class="payment-system-calculator-details">
		<div class="jobInvoice">
			 <label for="Invoice">Invoice number/Rego/Job Number</label><br>
	  		 <input type="text" id="Invoice" name="invoice">
	  		 <input type="hidden" id="percentage" value="<?php echo get_option( 'calculator_rate' );?>">
  		</div>
		<div class="jobvalue">
			 <label for="jobvalue">Job Value *</label><br>
	  		 <input type="number" id="jobvalue" class="cst-jobvalue" name="jobvalue"><br><br>
  		</div>
  		<div class="jobSurchargevalue">
			 <label for="Surcharge">Surcharge Value</label><br>
	  		 <input type="text" id="Surcharge" name="surcharge" readonly><br><br>
  		</div>
  		<div class="jobTotalvalue">
			 <label for="Total">Total Charge Amount</label><br>
	  		 <input type="text" id="Total" name="custom_price" readonly><br><br>
  		</div>
	</div>

</div>