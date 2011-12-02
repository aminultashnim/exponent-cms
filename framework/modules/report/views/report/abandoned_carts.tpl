{include file='menu.inc'}

{css unique="current_carts" corecss="tables"}

{/css}
	<div class="rightcol">
		<div class="module report abandoned_carts">
			{form action="abandoned_carts"}
			{"Abandoned Carts From:"|gettext}{br}
			{control type="dropdown" name="quickrange" label="" items=$quickrange default=$quickrange_default onchange="this.form.submit();"}      
			{/form}
			
			{br}
			<div class="exp-skin-table">
				<table border="0" cellspacing="0" cellpadding="0" width="50%">
					<thead>
						<th colspan="2">
							<h1 style="text-align: center;">{"Abandoned Cart Summary"|gettext}</h1>
						</th>
						</tr>
					</thead>
					<tbody>

						<tr class="odd">
							<td>{"Total No. of Carts"|gettext}</td>
							<td>{$summary.totalcarts}</td>
						</tr>
						<tr class="even">
							<td>{"Value of Products in the Carts"|gettext}</td>
							<td>{$summary.valueproducts}</td>
						</tr>
						<tr class="odd">
							<td>{"Active Carts w/out Products"|gettext}</td>
							<td>{$summary.cartsWithoutItems}</td>
						</tr>
						<tr class="even">
							<td>{"Active Carts w/ Products"|gettext}</td>
							<td>{$summary.cartsWithItems}</td>
						</tr>
						<tr class="odd">
							<td>{"Active Carts w/ Products and User Info"|gettext}</td>
							<td>{$summary.cartsWithItemsAndInfo}</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			{if $cartsWithoutItems|@count gt 1}
				{br}
				{br}
				<h2 style="text-align: center;">{"Abandoned Carts w/out Products and User Information"|gettext}</h2>
				<div class="exp-skin-table">
					<table border="0" cellspacing="0" cellpadding="0" width="50%">
						<thead>
							<tr>
								<th>Last Visit</th>
								<th>Referring URL</th>
							</tr>
						</thead>
						<tbody>
						{foreach from=$cartsWithoutItems item=item} 
						
							{if is_object($item)}
							<tr>
								<td>{$item->last_visit}</td>
							
								<td>
									{if $item->referrer}
										{$item->referrer}
									{else}
										Direct
									{/if}
								</td>
							</tr>
						
							{/if}
						{/foreach}
						</tbody>
					</table>
				</div>
			{/if}
			
			{if $cartsWithItems|@count gt 1}
				{br}
				{br}
				<h2 style="text-align: center;">{"Abandoned Carts w/ Products"|gettext}</h2>
				<div class="exp-skin-table">
					<table border="0" cellspacing="0" cellpadding="0" width="50%">
						<thead>
							<tr>
								<th>Last Visit</th>
								<th>Referring URL</th>
							</tr>
						</thead>
						<tbody>
						{foreach from=$cartsWithItems item=item} 
							{if is_array($item)}
							<tr>
								<td>{$item.last_visit}</td>
								
								<td>
									{if $item->referrer}
										{$item->referrer}
									{else}
										Direct
									{/if}
								</td>
							</tr>
							<tr>
								<table>
									<thead>
										<tr>
											<td colspan="3"><h3 style="margin:0; padding: 0;">Products</h3></td>
										</tr>
										<tr>
											<td><strong>Product Title</strong></td>
											<td><strong>Quantity</strong></td>
											<td><strong>Price</strong></td>
										</tr>
									</thead>
									<tbody>
									{foreach from=$item item=item2}  
										{if isset($item2->products_name)}
											<tr>
												<td>{$item2->products_name}</td>
												<td>{$item2->quantity}</td>
												<td>{$item2->products_price_adjusted}</td>
											</tr>
										{/if}
									{/foreach}
									</tbody>
								</table>
							</tr>
							{/if}
						{/foreach}
						</tbody>
					</table>
				</div>
			{/if}
			
					{if $cartsWithItemsAndInfo|@count gt 1}
			{br}
			{br}
			<h2 style="text-align: center;">{"Abandoned Carts w/ Products and User Information"|gettext}</h2>
			<div class="exp-skin-table">
				<table border="0" cellspacing="0" cellpadding="0" width="50%">
					<thead>
						<tr>
							<th>Name</th>
							<th>Email</th>
							<th>Last Visit</th>
							<th>Referring URL</th>
						</tr>
					</thead>
					<tbody>
					{foreach from=$cartsWithItemsAndInfo item=item} 
						{if is_array($item)}
						<tr>
							<td>{$item.name}</td>
							<td>{$item.email}</td>
							<td>{$item.last_visit}</td>
							<td>{$item.referrer}</td>
						</tr>
						<tr>
							<table>
								<thead>
									<tr>
										<td colspan="3"><h3 style="margin:0; padding: 0;">Products</h3></td>
									</tr>
									<tr>
										<td><strong>Product Title</strong></td>
										<td><strong>Quantity</strong></td>
										<td><strong>Price</strong></td>
									</tr>
								</thead>
								<tbody>
								{foreach from=$item item=item2}  
									{if isset($item2->products_name)}
										<tr>
											<td>{$item2->products_name}</td>
											<td>{$item2->quantity}</td>
											<td>{$item2->products_price_adjusted}</td>
										</tr>
									{/if}
								{/foreach}
								</tbody>
							</table>
						</tr>
						{/if}
					{/foreach}
					</tbody>
				</table>
			</div>
		{/if}

		</div>
    </div>
    <div style="clear:both"></div>
</div>