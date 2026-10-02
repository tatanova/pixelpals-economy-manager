
# [PixelPals/Economy-Manager]

> ⚠️ **Status: Archived / Unmaintained**  
> This is a legacy PHP project made for <a href="https://github.com/pmmp/PocketMine-MP">PMMP 3.0.0</a> (MCPE 0.15.10) and preserved for historical purposes only. It is no longer actively developed, updated, or monitored for security vulnerabilities.

</br>

<h2 align="center"><span>About</span></h2>
<p>This is a very rich economy manager plugin that even has a banking system, this plugin was developed for<br>old PixelPals and it features configuration so the server owners can adjust banking system more precisely<br>for their server economy system. Although, more features can be added by modifying the source code,<br>the plugin originally supported Discord Webhook integration to keep admins updated about the money<br>flow that was happening on the server to prevent blackmails/scams, however since the webhook does not<br>work anymore, it has been removed.</p>

</br>

<h2 align="center"><span>Commands</span></h2>

<h4>Cash</h4>

###### This command requires see.cash permission
```
/cash <player>
```

> Can be used to see others' cash

</br>

```
/cash
```

> Can be used to see the command sender's cash

</br>

<h4>Send</h4>

###### This command requires send.cash permission
```
/send <amount> <player>
```

> Can be used to send cash to other players, the \<amount\> argument must be numeric and greater than 0, the \<player\> argument must be valid and online player name

</br>

<h4>Bank</h4>

###### This command requires use.bank permission


```
/bank help
```

> Shows help menu for this command

</br>

```
/bank cash
```

> Shows the command sender's current bank cash, current interest percentage and the time that is remaining for the next increase

</br>

```
/bank deposit <amount>
```

> Can be used to deposit cash into the bank, \<amount\> must be numeric and greater than 0

</br>

```
/bank take <amount>
```

> Can be used to withdraw cash from the bank, \<amount\> must be numeric and greater than 0

</br>

```
/bank gamble <amount>
```

> Can be used to gamble with the money from the bank, there are 3 possible outcomes, in a win case the command sender gets 2.1 times more money, in a lose case they lose their money, there's a third case which is a tie and nothing happens in that case, also the \<amount\> argument must be numeric and greater than 0

</br>

<h4>Top</h4>

###### This command requires see.top permission
```
/top
```

> Shows the top ten richest players on the server

</br>

<h4>Eco</h4>

###### This command requires main.eco permission and is an admin command by default

```
/echo help
```

> Shows help menu for this command

</br>

```
/eco setcash <amount> <player>
```

> Can be used to set a player's cash to a custom value, however \<amount\> must be numeric and greater than 0, also \<player\> must be valid and online player name

</br>

```
/eco setbcash <amount> <player>
```

> Can be used to set a player's bank cash to a custom value, plus \<amount\> must be numeric and greater than 0, also \<player\> must be valid and online player name

</br>

```
/eco addcash <amount> <player>
```

> Can be used to add desired cash on top of a player's cash, and \<amount\> must be numeric and greater than 0, also \<player\> must be valid and online player name

</br>

```
/eco addbcash <amount> <player>
```

> Can be used to add desired cash on top of a player's bank cash, also \<amount\> must be numeric and greater than 0, and \<player\> must be valid and online player name

</br>

```
/eco removecash <amount> <player>
```

> Can be used to remove desired cash from a player's cash, also \<amount\> must be numeric and greater than 0 and less than or equal to player's cash, also \<player\> must be valid and online player name

</br>

```
/eco removebcash <amount> <player>
```

> Can be used to remove desired cash from a player's cash on the bank, also \<amount\> must be numeric and greater than 0 and less than or equal to player's cash on the bank, also \<player\> must be valid and online player name

</br>

```
/eco setitime <h> <m> <s>
```

> Can be used to temporarily override bank interest renew cycle, however this command is for testing purposes only and BankTimer task can override it, other than that, \<h\>, \<m\> and \<s\> must be a valid timer input, example __/eco setitime 15 35 29__ which will set the interest renew time to 15 hours 35 minutes 29 seconds

</br>

<h2 align="center"><span>Permissions</span></h2>

- **`see.cash`**
  - Default permission
- **`send.cash`**
  - Default permission
- **`use.bank`**
  - Default permission
- **`see.top`**
  - Default permission
- **`main.eco`**
  - Admin permission

</br>

<h2 align="center"><span>Config</span></h2>

```YAML
bank-interest-time: 24  # Interest add cycle time in hours, default is 24h
bank-interest: 1.8%  # Increase percentage
monetary-unit: ®  # Custom currency character
default-cash: 0   # Default starter cash for new players
default-bank-cash: 0  # Default starter deposit in bank for new players
```

</br>

<h2 align="center"><span>File activity</span></h2>

When enabled, the plugin will create:
<br>
- A YAML file: __~/PixelPals-Economy-Manager/config.yml__
- A compessed JSON file at __~/PixelPals-Economy-Manager/bank/bankTime.date__
- A compessed JSON file at __~/PixelPals-Economy-Manager/bank/bank.all__
- A compessed JSON file at __~/PixelPals-Economy-Manager/data/allData.eco__


</br>

<h2 align="center"><span>Task activity</span></h2>

When enabled, the plugin will create a task named BankTimer that runs once per second (once 20 tick)

</br>

<h2 align="center"><span>Affected events</span></h2>

- PlayerJoinEvent
- PlayerQuitEvent

</br>
</br>
</br>

<p align="center"><img src="https://media.tenor.com/o9rNU1uX_R0AAAAj/cat-campfire.gif" alt="cats" width="200"/></p>
<p align="center">
  <sub>Made with <b>0.1% AI</b> / <b>99.9% Human Code & Passion</b></sub><br>
  <sub><i>Preserved with pride from the PixelPals era.</i></sub>
</p>
